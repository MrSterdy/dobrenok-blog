<?php

use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\PartnerController;
use App\Http\Controllers\Api\EmployeeController;
use App\Http\Controllers\Api\EventController;
use App\Http\Controllers\Api\ApplicationController;
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

    // Posts API
    Route::get('/projects/{id}/posts', [PostController::class, 'index']);
    Route::get('/projects/{id}/posts/{slug}', [PostController::class, 'show']);
});
