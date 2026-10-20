<?php

use App\Http\Controllers\PaymentWebhookController;
use App\Http\Controllers\UdieApiController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Non-Custodial Payment Provider Webhooks & Real-Time Callbacks
|--------------------------------------------------------------------------
*/
Route::prefix('webhooks/payments')->middleware('throttle:120,1')->group(function () {
    Route::post('/mpesa/validation', [PaymentWebhookController::class, 'handleMpesaValidation'])->name('webhooks.mpesa.validation');
    Route::post('/{provider}/{tenantId?}', [PaymentWebhookController::class, 'handleWebhook'])->name('webhooks.payments.receive');
});

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
