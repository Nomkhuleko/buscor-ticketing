<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RouteRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        /*
        |--------------------------------------------------------------------------
        | Get Current Route ID
        |--------------------------------------------------------------------------
        |
        | During an update, Laravel's route model binding may provide either
        | the Route model itself or the UUID.
        |
        */

        $route = $this->route('route');

        $routeId = is_object($route)
            ? $route->id
            : $route;

        return [
            /*
            |--------------------------------------------------------------------------
            | Area
            |--------------------------------------------------------------------------
            */

            'area_id' => [
                'required',
                'uuid',
                'exists:areas,id',
            ],

            /*
            |--------------------------------------------------------------------------
            | Municipality
            |--------------------------------------------------------------------------
            */

            'municipality_id' => [
                'required',
                'uuid',
                'exists:municipalities,id',
            ],

            /*
            |--------------------------------------------------------------------------
            | Depot
            |--------------------------------------------------------------------------
            */

            'depot_id' => [
                'nullable',
                'uuid',
                'exists:depots,id',
            ],

            /*
            |--------------------------------------------------------------------------
            | Route Code
            |--------------------------------------------------------------------------
            */

            'route_code' => [
                'required',
                'string',
                'max:20',

                Rule::unique('routes', 'route_code')
                    ->ignore($routeId),
            ],

            /*
            |--------------------------------------------------------------------------
            | Route Name
            |--------------------------------------------------------------------------
            */

            'route_name' => [
                'required',
                'string',
                'max:255',
            ],

            /*
            |--------------------------------------------------------------------------
            | Origin / Starting Location
            |--------------------------------------------------------------------------
            */

            'from_location' => [
                'required',
                'string',
                'max:255',
            ],

            /*
            |--------------------------------------------------------------------------
            | Destination
            |--------------------------------------------------------------------------
            */

            'to_location' => [
                'required',
                'string',
                'max:255',

                'different:from_location',
            ],

            /*
            |--------------------------------------------------------------------------
            | Distance
            |--------------------------------------------------------------------------
            */

            'distance_km' => [
                'nullable',
                'numeric',
                'min:0',
                'max:999999.99',
            ],

            /*
            |--------------------------------------------------------------------------
            | Estimated Duration
            |--------------------------------------------------------------------------
            |
            | Stored in minutes.
            |
            */

            'estimated_duration' => [
                'nullable',
                'integer',
                'min:1',
                'max:1440',
            ],

            /*
            |--------------------------------------------------------------------------
            | Active Status
            |--------------------------------------------------------------------------
            */

            'active' => [
                'sometimes',
                'boolean',
            ],

            /*
            |--------------------------------------------------------------------------
            | Description
            |--------------------------------------------------------------------------
            */

            'description' => [
                'nullable',
                'string',
                'max:5000',
            ],
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'area_id.required' =>
                'The route area is required.',

            'area_id.uuid' =>
                'The selected area is invalid.',

            'area_id.exists' =>
                'The selected area does not exist.',

            'municipality_id.required' =>
                'The route municipality is required.',

            'municipality_id.uuid' =>
                'The selected municipality is invalid.',

            'municipality_id.exists' =>
                'The selected municipality does not exist.',

            'depot_id.uuid' =>
                'The selected depot is invalid.',

            'depot_id.exists' =>
                'The selected depot does not exist.',

            'route_code.required' =>
                'The route code is required.',

            'route_code.max' =>
                'The route code may not be greater than 20 characters.',

            'route_code.unique' =>
                'This route code is already in use.',

            'route_name.required' =>
                'The route name is required.',

            'route_name.max' =>
                'The route name may not be greater than 255 characters.',

            'from_location.required' =>
                'The starting location is required.',

            'from_location.max' =>
                'The starting location may not be greater than 255 characters.',

            'to_location.required' =>
                'The destination is required.',

            'to_location.max' =>
                'The destination may not be greater than 255 characters.',

            'to_location.different' =>
                'The destination must be different from the starting location.',

            'distance_km.numeric' =>
                'The distance must be a valid number.',

            'distance_km.min' =>
                'The distance cannot be negative.',

            'estimated_duration.integer' =>
                'The estimated duration must be a whole number of minutes.',

            'estimated_duration.min' =>
                'The estimated duration must be at least 1 minute.',

            'estimated_duration.max' =>
                'The estimated duration may not exceed 24 hours.',

            'active.boolean' =>
                'The active status must be true or false.',

            'description.max' =>
                'The route description may not be greater than 5000 characters.',
        ];
    }

    /**
     * Prepare data before validation.
     */
    protected function prepareForValidation(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Normalize Route Code
        |--------------------------------------------------------------------------
        |
        | Route codes should be stored consistently.
        |
        */

        if ($this->filled('route_code')) {
            $this->merge([
                'route_code' => strtoupper(
                    trim($this->route_code)
                ),
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Normalize Location Values
        |--------------------------------------------------------------------------
        */

        if ($this->filled('from_location')) {
            $this->merge([
                'from_location' => trim(
                    $this->from_location
                ),
            ]);
        }

        if ($this->filled('to_location')) {
            $this->merge([
                'to_location' => trim(
                    $this->to_location
                ),
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Normalize Route Name
        |--------------------------------------------------------------------------
        */

        if ($this->filled('route_name')) {
            $this->merge([
                'route_name' => trim(
                    $this->route_name
                ),
            ]);
        }
    }
}