<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\TaskController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\AuthController;

// Simple health/test endpoint
Route::get('/test', fn () => response()->json(['message' => 'Hola!, Bloomingtec API!']));

// Authentication endpoints (JWT to be implemented)
Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    // The following will be protected by auth:api once JWT is configured
    Route::middleware('auth:api')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('me', [AuthController::class, 'me']);
        Route::post('refresh', [AuthController::class, 'refresh']);
    });
});

// Protected resources (wrap with auth:api after JWT setup)
Route::middleware('auth:api')->group(function () {
    Route::apiResource('tasks', TaskController::class)->only(['index','show','store','update','destroy']);
    Route::apiResource('users', UserController::class)->only(['store','update','destroy']);
});
