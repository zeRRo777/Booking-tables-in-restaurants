<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\TestController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VerifyController;
use App\Http\Middleware\GuestMiddleware;
use App\Http\Middleware\ValidateTokenInDatabase;
use Illuminate\Support\Facades\Route;

Route::middleware('throttle:api')->group(function () {
    Route::get('/health', [TestController::class, 'health']);

    Route::middleware(GuestMiddleware::class)->group(function () {

        Route::controller(AuthController::class)->group(function () {
            Route::post('/auth/register', 'register');

            Route::post('/auth/login', 'login');

            Route::post('/auth/password/reset',  'preperationResetPassword');

            Route::post('/auth/password/reset/confirm', 'resetPassword');
        });
    });

    Route::middleware(['auth:api', ValidateTokenInDatabase::class])->group(function () {

        Route::controller(UserController::class)->group(function () {
            Route::get('/me', 'profile');
            Route::patch('/me', 'updateMe');
            Route::delete('/me', 'deleteMe');
        });

        Route::controller(AuthController::class)->group(function () {
            Route::post('/auth/email/change', 'prepareChangeEmail');
            Route::post('/auth/password/change', 'changePassword');
            Route::post('/auth/phone/change', 'prepareChangePhone');
            Route::post('/auth/phone/change/confirm', 'changePhone');
            Route::post('/auth/logout', 'logout');
        });

        Route::controller(VerifyController::class)->group(function () {
            Route::post('/verify/email/send', 'prepareEmailVerify');
            Route::post('/verify/email/confirm', 'verifyEmail');
            Route::post('/verify/phone/send', 'preparePhoneVerify');
            Route::post('/verify/phone/confirm', 'verifyPhone');
        });
    });

    Route::post('/auth/email/change/confirm', [AuthController::class, 'changeEmail']);
});
