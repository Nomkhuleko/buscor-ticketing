<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Fare extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'route_id',
        'from',
        'to',
        'one_way_fare',
        'fare_5_day',
        'fare_6_day',
        'fare_7_day',
        'fare_22_day',
        'fare_26_day',
        'active',
    ];

    protected $casts = [
        'one_way_fare' => 'decimal:2',
        'fare_5_day' => 'decimal:2',
        'fare_6_day' => 'decimal:2',
        'fare_7_day' => 'decimal:2',
        'fare_22_day' => 'decimal:2',
        'fare_26_day' => 'decimal:2',
        'active' => 'boolean',
    ];

    public function route()
    {
        return $this->belongsTo(Route::class);
    }
}