<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\TestController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\GuestMiddleware;
use Illuminate\Support\Facades\Route;

Route::get('/health', [TestController::class, 'health']);

Route::middleware(GuestMiddleware::class)->group(function () {

    Route::post('/register', [AuthController::class, 'register']);

    Route::post('/login', [AuthController::class, 'login']);
});
