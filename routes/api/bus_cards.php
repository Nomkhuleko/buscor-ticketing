<?php

use Illuminate\Support\Facades\Route;

Route::prefix('bus-cards')->group(function () {

    Route::post('/topup', []);

    Route::post('/validate', []);

    Route::get('/{cardNumber}', []);

});