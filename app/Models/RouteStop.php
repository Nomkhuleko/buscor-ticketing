<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RouteStop extends Model
{
    public function route()
{
    return $this->belongsTo(Route::class);
}

public function stop()
{
    return $this->belongsTo(Stop::class);
}
}
