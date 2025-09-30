<?php

use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\PartnerController;
use App\Http\Controllers\Api\EmployeeController;
use App\Http\Controllers\Api\EventController;
use App\Http\Controllers\Api\ApplicationController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\SubscriptionController;
use App\Http\Controllers\Api\WebhookController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    // Projects API
    Route::get('/projects', [ProjectController::class, 'index']);
    Route::get('/projects/{id}', [ProjectController::class, 'show']);

    // Project relations API
    Route::get('/projects/{id}/partners', [PartnerController::class, 'index']);
    Route::get('/projects/{id}/employees', [EmployeeController::class, 'index']);
    Route::get('/projects/{id}/events', [EventController::class, 'index']);
    Route::get('/projects/{id}/applications', [ApplicationController::class, 'index']);
    Route::post('/projects/{id}/applications', [ApplicationController::class, 'store']);
    Route::post('/projects/{id}/payments', [PaymentController::class, 'store']);
    Route::post('/projects/{id}/subscriptions', [SubscriptionController::class, 'store']);

    // Posts API
    Route::get('/projects/{id}/posts', [PostController::class, 'index']);
    Route::get('/projects/{id}/posts/{slug}', [PostController::class, 'show']);

    // Webhooks API
    Route::post('/webhooks/payments/{provider}', [WebhookController::class, 'handlePaymentWebhook']);
});
