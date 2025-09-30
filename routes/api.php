<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\TestController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\GuestMiddleware;
use App\Http\Middleware\ValidateTokenInDatabase;
use Illuminate\Support\Facades\Route;

Route::get('/health', [TestController::class, 'health']);

Route::middleware(GuestMiddleware::class)->group(function () {

    Route::post('/auth/register', [AuthController::class, 'register']);

    Route::post('/auth/login', [AuthController::class, 'login']);
});

Route::middleware(['auth:api', ValidateTokenInDatabase::class])->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    Route::controller(UserController::class)->group(function () {
        Route::get('/me', 'profile');
        Route::patch('/me', 'updateMe');
        Route::delete('/me', 'deleteMe');
    });

    Route::post('/auth/password/change', [AuthController::class, 'changePassword']);
});
