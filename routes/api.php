<?php

use App\Http\Controllers\Api\ClassApiController;
use Illuminate\Support\Facades\Route;

Route::middleware('api.key')->group(function () {
    Route::get('/classes', [ClassApiController::class, 'index']);
    Route::get('/classes/{class}', [ClassApiController::class, 'show']);
    Route::post('/classes/{class}/registrants', [ClassApiController::class, 'storeRegistrant']);
});
