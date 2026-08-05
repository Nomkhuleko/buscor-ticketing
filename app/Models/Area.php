<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Area extends Model
{
   public function municipalities()
{
    return $this->hasMany(Municipality::class);
}

public function routes()
{
    return $this->hasMany(Route::class);
}
}
