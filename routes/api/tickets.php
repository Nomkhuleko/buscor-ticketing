<?php

use Illuminate\Support\Facades\Route;

Route::prefix('tickets')->group(function () {

    Route::post('/purchase', []);

    Route::post('/validate', []);

    Route::get('/track/{reference}', []);

    Route::get('/download/{reference}', []);

});