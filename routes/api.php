<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\TestController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\GuestMiddleware;
use App\Http\Middleware\ValidateTokenInDatabase;
use Illuminate\Support\Facades\Route;

Route::middleware('throttle:api')->group(function () {
    Route::get('/health', [TestController::class, 'health']);

    Route::middleware(GuestMiddleware::class)->group(function () {

        Route::post('/auth/register', [AuthController::class, 'register']);

        Route::post('/auth/login', [AuthController::class, 'login']);

        Route::post('/auth/password/reset', [AuthController::class, 'preperationResetPassword']);

        Route::post('/auth/password/reset/confirm', [AuthController::class, 'resetPassword']);
    });

    Route::middleware(['auth:api', ValidateTokenInDatabase::class])->group(function () {
        Route::post('/auth/logout', [AuthController::class, 'logout']);

        Route::controller(UserController::class)->group(function () {
            Route::get('/me', 'profile');
            Route::patch('/me', 'updateMe');
            Route::delete('/me', 'deleteMe');
        });

        Route::post('/auth/email/change', [AuthController::class, 'prepareChangeEmail']);
        Route::post('/auth/password/change', [AuthController::class, 'changePassword']);
    });
});
