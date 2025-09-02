<?php

use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\ProjectController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    // Projects API
    Route::get('/projects', [ProjectController::class, 'index']);
    Route::get('/projects/{id}', [ProjectController::class, 'show']);

    // Posts API
    Route::get('/posts', [PostController::class, 'index']);
    Route::get('/posts/{slug}', [PostController::class, 'show']);
});
