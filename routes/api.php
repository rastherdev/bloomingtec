<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\TaskController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\AuthController;

// Simple health/test endpoint
Route::get('/test', fn () => response()->json(['message' => 'Hola!, Bloomingtec API!']));
 
Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    Route::middleware('auth:api')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('me', [AuthController::class, 'me']);
        Route::post('refresh', [AuthController::class, 'refresh']);
    });
});

Route::middleware('auth:api')->group(function () {
    Route::apiResource('tasks', TaskController::class)->only(['index','show','store','update','destroy']);
    Route::apiResource('users', UserController::class)->only(['store','update','destroy']);
});
