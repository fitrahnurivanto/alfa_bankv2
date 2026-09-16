<?php

use App\Http\Controllers\Api\ClassApiController;
use App\Http\Controllers\Api\MasterDataApiController;
use Illuminate\Support\Facades\Route;

Route::middleware('api.key')->group(function () {
    Route::get('/classes', [ClassApiController::class, 'index']);
    Route::get('/classes/{class}', [ClassApiController::class, 'show']);
    Route::post('/classes/{class}/registrants', [ClassApiController::class, 'storeRegistrant']);
    Route::post('/private-class-requests', [ClassApiController::class, 'storePrivateClassRequest']);
    Route::get('/trainers', [MasterDataApiController::class, 'trainers']);
    Route::get('/trainers/{trainer}', [MasterDataApiController::class, 'showTrainer']);
    Route::get('/trainings', [MasterDataApiController::class, 'trainings']);
});
