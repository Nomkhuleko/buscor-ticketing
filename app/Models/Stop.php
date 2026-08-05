<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Stop extends Model
{
    public function routes()
{
    return $this->belongsToMany(
        Route::class,
        'route_stops'
    )
    ->withPivot([
        'stop_order',
        'distance_from_start'
    ]);
}
}
