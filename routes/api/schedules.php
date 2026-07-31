<?php

use Illuminate\Support\Facades\Route;

Route::prefix('schedules')->group(function () {

    Route::get('/', []);
    Route::get('/{id}', []);
    Route::post('/', []);
    Route::put('/{id}', []);
    Route::delete('/{id}', []);

});