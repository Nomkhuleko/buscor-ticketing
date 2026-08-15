<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RouteResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [

            /*
            |--------------------------------------------------------------------------
            | Route Identification
            |--------------------------------------------------------------------------
            */

            'id' => $this->id,

            'route_code' => $this->route_code,

            'route_name' => $this->route_name,


            /*
            |--------------------------------------------------------------------------
            | Route Details
            |--------------------------------------------------------------------------
            */

            'from_location' => $this->from_location,

            'to_location' => $this->to_location,

            'distance_km' => $this->distance_km !== null
                ? (float) $this->distance_km
                : null,

            'estimated_duration' => $this->estimated_duration !== null
                ? (int) $this->estimated_duration
                : null,

            'description' => $this->description,


            /*
            |--------------------------------------------------------------------------
            | Status
            |--------------------------------------------------------------------------
            */

            'active' => (bool) $this->active,

            'status' => $this->active
                ? 'active'
                : 'inactive',


            /*
            |--------------------------------------------------------------------------
            | Foreign Keys
            |--------------------------------------------------------------------------
            */

            'area_id' => $this->area_id,

            'municipality_id' => $this->municipality_id,

            'depot_id' => $this->depot_id,


            /*
            |--------------------------------------------------------------------------
            | Area
            |--------------------------------------------------------------------------
            */

            'area' => $this->whenLoaded('area', function () {

                if (!$this->area) {
                    return null;
                }

                return [
                    'id' => $this->area->id,
                    'name' => $this->area->name,
                ];
            }),


            /*
            |--------------------------------------------------------------------------
            | Municipality
            |--------------------------------------------------------------------------
            */

            'municipality' => $this->whenLoaded('municipality', function () {

                if (!$this->municipality) {
                    return null;
                }

                return [
                    'id' => $this->municipality->id,
                    'name' => $this->municipality->name,
                ];
            }),


            /*
            |--------------------------------------------------------------------------
            | Depot
            |--------------------------------------------------------------------------
            */

            'depot' => $this->whenLoaded('depot', function () {

                if (!$this->depot) {
                    return null;
                }

                return [
                    'id' => $this->depot->id,
                    'name' => $this->depot->name,
                ];
            }),


            /*
            |--------------------------------------------------------------------------
            | Stops
            |--------------------------------------------------------------------------
            */

            'stops' => $this->whenLoaded('stops', function () {

                return $this->stops->map(function ($stop) {

                    return [
                        'id' => $stop->id,

                        'name' => $stop->name,

                        'code' => $stop->code ?? null,

                        'location' => $stop->location ?? null,

                        'latitude' => $stop->latitude !== null
                            ? (float) $stop->latitude
                            : null,

                        'longitude' => $stop->longitude !== null
                            ? (float) $stop->longitude
                            : null,

                        'stop_order' => $stop->pivot?->stop_order,

                        'distance_from_start' =>
                            $stop->pivot?->distance_from_start !== null
                                ? (float) $stop->pivot->distance_from_start
                                : null,

                        'active' => isset($stop->active)
                            ? (bool) $stop->active
                            : null,
                    ];
                })->values();
            }),


            /*
            |--------------------------------------------------------------------------
            | Schedules
            |--------------------------------------------------------------------------
            */

            'schedules' => $this->whenLoaded('schedules', function () {

                return $this->schedules->map(function ($schedule) {

                    return [
                        'id' => $schedule->id,

                        'route_id' => $schedule->route_id,

                        'departure_time' =>
                            $schedule->departure_time,

                        'arrival_time' =>
                            $schedule->arrival_time,

                        'day_type' =>
                            $schedule->day_type,

                        'active' =>
                            (bool) $schedule->active,

                        'description' =>
                            $schedule->description,
                    ];
                })->values();
            }),


            /*
            |--------------------------------------------------------------------------
            | Fares
            |--------------------------------------------------------------------------
            */

            'fares' => $this->whenLoaded('fares', function () {

                return $this->fares->map(function ($fare) {

                    return [
                        'id' => $fare->id,

                        'route_id' => $fare->route_id,

                        'from' => $fare->from,

                        'to' => $fare->to,

                        'one_way_fare' =>
                            $fare->one_way_fare !== null
                                ? (float) $fare->one_way_fare
                                : null,

                        'fare_5_day' =>
                            $fare->fare_5_day !== null
                                ? (float) $fare->fare_5_day
                                : null,

                        'fare_6_day' =>
                            $fare->fare_6_day !== null
                                ? (float) $fare->fare_6_day
                                : null,

                        'fare_7_day' =>
                            $fare->fare_7_day !== null
                                ? (float) $fare->fare_7_day
                                : null,

                        'fare_22_day' =>
                            $fare->fare_22_day !== null
                                ? (float) $fare->fare_22_day
                                : null,

                        'fare_26_day' =>
                            $fare->fare_26_day !== null
                                ? (float) $fare->fare_26_day
                                : null,

                        'active' =>
                            (bool) $fare->active,
                    ];
                })->values();
            }),


            /*
            |--------------------------------------------------------------------------
            | Timestamps
            |--------------------------------------------------------------------------
            */

            'created_at' => $this->created_at?->toISOString(),

            'updated_at' => $this->updated_at?->toISOString(),

            'deleted_at' => $this->deleted_at?->toISOString(),
        ];
    }
}