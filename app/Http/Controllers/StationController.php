<?php
/**
 * @version    1.0.0
 * @package    flopspot
 * @copyright  Copyright (C) 2020 Sven Ahrens
 * @license    MIT
 */

namespace App\Http\Controllers;

use App\Http\Requests\StationRequest;
use App\Service\Timetable;
use Illuminate\Http\JsonResponse;

/**
 * Handles arrival and departure stations from the Timetables api by the Deutsche Bahn
 *
 * Class StationController
 * @package App\Http\Controllers
 */
class StationController extends Controller
{
    /**
     * Returns arrival and departure stations
     *
     * @param StationRequest $request
     * @return JsonResponse
     */
    public function index(StationRequest $request): JsonResponse
    {
        $timetable = new Timetable($request->all());

        $stations = $timetable->getStations();

        var_dump($stations);

        return response()->json([], 200);
    }
}
