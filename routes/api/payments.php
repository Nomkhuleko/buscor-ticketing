<?php

use Illuminate\Support\Facades\Route;

Route::prefix('payments')->group(function () {

    Route::post('/', []);

    Route::post('/callback', []);

    Route::get('/status/{reference}', []);

});