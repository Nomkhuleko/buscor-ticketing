<?php

use Illuminate\Support\Facades\Route;

Route::prefix('routes')->group(function () {

    Route::get('/', []);
    Route::get('/{id}', []);
    Route::post('/', []);
    Route::put('/{id}', []);
    Route::delete('/{id}', []);

});