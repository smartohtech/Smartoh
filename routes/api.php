<?php

use App\Http\Controllers\UdieApiController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| UDIE RESTful APIs
|--------------------------------------------------------------------------
*/
Route::prefix('udie')->group(function () {
    Route::get('/devices', [UdieApiController::class, 'index']);
    Route::get('/devices/{id}', [UdieApiController::class, 'show']);
    Route::post('/discover', [UdieApiController::class, 'discover']);
    Route::post('/evidence/ingest', [UdieApiController::class, 'ingestEvidence']);
    Route::get('/devices/{id}/evidence', [UdieApiController::class, 'evidence']);
    Route::get('/devices/{id}/topology', [UdieApiController::class, 'topology']);
    Route::get('/devices/{id}/capabilities', [UdieApiController::class, 'capabilities']);
    Route::get('/device-models', [UdieApiController::class, 'deviceModels']);
});
