<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\TestController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\GuestMiddleware;
use App\Http\Middleware\ValidateTokenInDatabase;
use Illuminate\Support\Facades\Route;

Route::get('/health', [TestController::class, 'health']);

Route::middleware(GuestMiddleware::class)->group(function () {

    Route::post('/register', [AuthController::class, 'register']);

    Route::post('/login', [AuthController::class, 'login']);
});

Route::middleware(['auth:api', ValidateTokenInDatabase::class])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/me', [UserController::class, 'profile']);

    Route::patch('/me', [UserController::class, 'updateMe']);
});
