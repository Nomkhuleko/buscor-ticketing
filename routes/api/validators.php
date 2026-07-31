<?php

use Illuminate\Support\Facades\Route;

Route::prefix('validators')->group(function () {

    Route::post('/scan-ticket', []);

    Route::post('/scan-card', []);

    Route::post('/heartbeat', []);

    Route::get('/history', []);

});