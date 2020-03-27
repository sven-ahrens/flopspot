<?php
/**
 * @version    1.0.0
 * @package    flopspot
 * @copyright  Copyright (C) 2020 Sven Ahrens
 * @license    MIT
 */

namespace App\Service;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
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

    public function getStations(): array
    {
        $stations = [
            'arrival' => [],
            'departure' => []
        ];
        $stops = $this->getTrainStops($this->getStationIdentifier());

        foreach ($stops as $stop) {
            // c is the type of train. RE, ICE, IC for instance
            $line = $stop->tl['c'];

            // ar and dp (arrival, departure) include some meta information about which line that train is. (24, 105..)
            if ($stop->ar) {
                $line .= ' ' . $stop->ar['l'];
            } else {
                $line .= ' ' . $stop->dp['l'];
            }

            if ($line !== $this->train) {
                continue;
            }
        }

        return $stations;
    }
}
