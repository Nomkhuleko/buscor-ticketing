<?php

use Illuminate\Support\Facades\Route;

Route::prefix('reports')->group(function () {

    Route::get('/daily');

    Route::get('/monthly');

    Route::get('/tickets');

    Route::get('/payments');

    Route::get('/validators');

});