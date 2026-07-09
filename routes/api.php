<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PostController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Authentication routes with strict throttling (5 attempts per minute)
Route::middleware('throttle:auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
});

// Public post routes (read-only) with general throttling (60 per minute)
Route::middleware('throttle:api')->group(function () {
    Route::get('/posts', [PostController::class, 'index']);
    Route::get('/posts/{post:slug}', [PostController::class, 'show']);
});

// Protected routes with authenticated user throttling (100 per minute)
Route::middleware(['auth:sanctum', 'throttle:authenticated'])->group(function () {
    // Auth routes
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Post management routes (requires authentication)
    Route::post('/posts', [PostController::class, 'store']);
    Route::put('/posts/{post:slug}', [PostController::class, 'update']);
    Route::patch('/posts/{post:slug}', [PostController::class, 'update']);
    Route::delete('/posts/{post:slug}', [PostController::class, 'destroy']);
});

