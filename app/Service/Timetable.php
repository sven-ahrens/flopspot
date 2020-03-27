<?php
/**
 * @version    1.0.0
 * @package    flopspot
 * @copyright  Copyright (C) 2020 Sven Ahrens
 * @license    MIT
 */

namespace App\Service;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use SimpleXMLElement;

/**
 * Class Timetable
 * @package App\Service
 */
class Timetable
{
    /**
     * @var string
     */
    private $departure;
    /**
     * @var string
     */
    private $train;
    /**
     * @var string
     */
    private $station;
    /**
     * @var PendingRequest
     */
    private $api;

    /**
     * Timetable constructor.
     * @param array $data  Any necessary data for the timetable api
     */
    public function __construct(array $data)
    {
        $this->departure = $data['departure'];
        $this->train = $data['train'];
        $this->station = $data['station'];
        $this->api = Http::withToken(env('APP_AUTH_TOKEN'))
            ->baseUrl('http://api.deutschebahn.com/timetables/v1/');
    }

    /**
     * Get the eva number of a station to get the timetable plan for a certain date
     *
     * @return string
     */
    public function getStationIdentifier(): string
    {
        $response = $this->api->get('station/' . $this->station);
        $stations = simplexml_load_string($response);

        return (string) $stations->station['eva'];
    }

    /**
     * Get any trains who start or end at the given station
     * @param $id
     * @return SimpleXMLElement
     */
    public function getTrainStops(string $id): SimpleXMLElement
    {
        $response = $this->api->get('plan/' . $id . '/' . implode('/', $this->departure));

        return simplexml_load_string($response);
    }

    /**
     * @return Collection
     */
    public function getStationsByTrain(): Collection
    {
        $trains = collect([]);
        $stops = $this->getTrainStops($this->getStationIdentifier());

        foreach ($stops as $stop) {
            [$keys, $values] = Arr::divide((array) $stop);
            $trainType = '';

            foreach ($keys as $index => $key) {
                if ($key === 'tl') {
                    $trainType = $values[$index]['c'];
                    continue;
                }

                if ($values[$index] instanceof SimpleXMLElement) {
                    $train = $trainType . ' ' . $values[$index]['l'];
                    $time = Str::of($values[$index]['pt'])->substr(6);

                    $values[$index]->addAttribute('time', $time);

                    if ($train === $this->train) {
                        $trains->add($values[$index]);
                    }
                }
            }
        }

        return $trains;
    }

    /**
     * @param Collection $stations
     * @return Collection
     */
    public function getStationByUserDate(Collection $stations): Collection
    {
        $userDate = Carbon::now()->setHours($this->departure['time']->__toString())->setMinutes(0);

        return $stations->filter(function ($station) use ($userDate) {
            $time = str_split($station['time'], 2);
            $stationDate = Carbon::now()->setHours($time[0])->setMinutes($time[1]);

            return $userDate->diffInMinutes($stationDate) < 30;
        });
    }
}
