<?php

use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {

    Route::post('/login', []);
    Route::post('/logout', []);
    Route::post('/forgot-password', []);
    Route::post('/reset-password', []);

});