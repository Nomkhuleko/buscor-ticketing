<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Route extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    /**
     * Primary Key
     */
    protected $keyType = 'string';

    public $incrementing = false;

    /**
     * Mass Assignable Attributes
     */
    protected $fillable = [
        'area_id',
        'municipality_id',
        'depot_id',
        'route_code',
        'route_name',
        'from_location',
        'to_location',
        'distance_km',
        'estimated_duration',
        'active',
        'description',
    ];

    /**
     * Attribute Casting
     */
    protected $casts = [
        'active' => 'boolean',
        'distance_km' => 'decimal:2',
        'estimated_duration' => 'integer',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    /**
     * Area
     */
    public function area()
    {
        return $this->belongsTo(Area::class);
    }

    /**
     * Municipality
     */
    public function municipality()
    {
        return $this->belongsTo(Municipality::class);
    }

    /**
     * Depot
     */
    public function depot()
    {
        return $this->belongsTo(Depot::class);
    }

    /**
     * Stops
     */
    public function stops()
    {
        return $this->belongsToMany(
            Stop::class,
            'route_stops'
        )
            ->withPivot([
                'stop_order',
                'distance_from_start',
            ])
            ->withTimestamps()
            ->orderBy('route_stops.stop_order');
    }

    /**
     * Schedules
     */
    public function schedules()
    {
        return $this->hasMany(Schedule::class);
    }

    /**
     * Fares
     */
    public function fares()
    {
        return $this->hasMany(Fare::class);
    }

    /**
     * Tickets
     */
    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

    /**
     * Bus Cards
     */
    public function busCards()
    {
        return $this->hasMany(BusCard::class);
    }

    /**
     * Validator Logs
     */
    public function validatorLogs()
    {
        return $this->hasMany(ValidatorLog::class);
    }
}