<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Municipality extends Model
{
    public function area()
{
    return $this->belongsTo(Area::class);
}

public function routes()
{
    return $this->hasMany(Route::class);
}
}
