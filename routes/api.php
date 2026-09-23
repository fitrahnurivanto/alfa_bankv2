<?php

use App\Http\Controllers\Api\ClassApiController;
use App\Http\Controllers\Api\MasterDataApiController;
use App\Http\Controllers\Api\IntegrationApiController;
use Illuminate\Support\Facades\Route;

Route::middleware('api.key')->group(function () {
    Route::get('/classes', [ClassApiController::class, 'index']);
    Route::get('/classes/{class}', [ClassApiController::class, 'show']);
    Route::post('/classes/{class}/registrants', [ClassApiController::class, 'storeRegistrant']);
    Route::post('/private-class-requests', [ClassApiController::class, 'storePrivateClassRequest']);
    Route::get('/trainers', [MasterDataApiController::class, 'trainers']);
    Route::get('/trainers/{trainer}', [MasterDataApiController::class, 'showTrainer']);
    Route::get('/trainings', [MasterDataApiController::class, 'trainings']);
    Route::get('/classes/{class}/sessions', [IntegrationApiController::class, 'sessions']);
    Route::get('/classes/{class}/trainer-attendance', [IntegrationApiController::class, 'trainerAttendance']);
    Route::get('/classes/{class}/grade-file', [IntegrationApiController::class, 'gradeFile']);
    Route::post('/payments', [IntegrationApiController::class, 'payment']);
    Route::post('/sso/consume', [IntegrationApiController::class, 'consumeSso']);
});
