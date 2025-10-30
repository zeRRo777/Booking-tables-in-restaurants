<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ChainController;
use App\Http\Controllers\ReminderTypeController;
use App\Http\Controllers\ReservationStatuseController;
use App\Http\Controllers\RestaurantController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\TableController;
use App\Http\Controllers\TestController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VerifyController;
use App\Http\Middleware\GuestMiddleware;
use App\Http\Middleware\ValidateTokenInDatabase;
use App\Models\ReminderType;
use App\Models\ReservationStatuse;
use App\Models\Restaurant;
use App\Models\RestaurantChain;
use App\Models\Review;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Route;

Route::middleware('throttle:api')->group(function (): void {
    Route::get('/health', [TestController::class, 'health']);

    Route::middleware(GuestMiddleware::class)->group(function (): void {

        Route::controller(AuthController::class)->group(function (): void {
            Route::post('/auth/register', 'register');

            Route::post('/auth/login', 'login');

            Route::post('/auth/password/reset',  'preperationResetPassword');

            Route::post('/auth/password/reset/confirm', 'resetPassword');
        });
    });

    Route::middleware(['auth:api', ValidateTokenInDatabase::class])->group(function (): void {

        Route::controller(UserController::class)->group(function (): void {
            Route::get('/me', 'profile');
            Route::patch('/me', 'updateMe');
            Route::delete('/me', 'deleteMe');
            Route::get('/users', 'index')->can('viewAny', User::class);
            Route::get('/users/{id}', 'show')->can('view', User::class);
            Route::post('/users', 'store')->can('create', User::class);
            Route::patch('/users/{id}', 'update')->can('update', User::class);
            Route::delete('/users/{id}', 'destroy')->can('delete', User::class);
            Route::post('/users/{id}/role', 'addRole')->can('addRole', User::class);
            Route::delete('/users/{user_id}/role/{role_id}', 'removeRole')->can('removeRole', User::class);
        });

        Route::controller(AuthController::class)->group(function (): void {
            Route::post('/auth/email/change', 'prepareChangeEmail');
            Route::post('/auth/password/change', 'changePassword');
            Route::post('/auth/phone/change', 'prepareChangePhone');
            Route::post('/auth/phone/change/confirm', 'changePhone');
            Route::post('/auth/logout', 'logout');
        });

        Route::controller(VerifyController::class)->group(function (): void {
            Route::post('/verify/email/send', 'prepareEmailVerify');
            Route::post('/verify/email/confirm', 'verifyEmail');
            Route::post('/verify/phone/send', 'preparePhoneVerify');
            Route::post('/verify/phone/confirm', 'verifyPhone');
        });

        Route::controller(RoleController::class)->group(function (): void {
            Route::get('/roles', 'index')->can('viewAny', Role::class);
            Route::get('/roles/{id}', 'show')->can('view', Role::class);
            Route::post('/roles', 'store')->can('create', Role::class);
            Route::patch('/roles/{id}', 'update')->can('update', Role::class);
            Route::delete('/roles/{id}', 'destroy')->can('delete', Role::class);
        });

        Route::controller(ChainController::class)->group(function () {
            Route::post('/chains', 'store')->can('create', RestaurantChain::class);
            Route::patch('/chains/{id}', 'update');
            Route::delete('/chains/{id}', 'destroy')->can('delete', RestaurantChain::class);
            Route::get('/chains/{id}/admins', 'allAdmins');
        });

        Route::controller(RestaurantController::class)->group(function () {
            Route::post('/restaurants', 'store')->can('create', Restaurant::class);
            Route::patch('/restaurants/{id}', 'update');
            Route::delete('/restaurants/{id}', 'destroy');
            Route::patch('/restaurants/{id}/status', 'changeStatus')->can('changeStatus', Restaurant::class);
            Route::get('/restaurants/{id}/schedules', 'restaurantSchedules');
            Route::get('/restaurants/{id}/schedules/{date}', 'resraurantSchedule');
            Route::post('/restaurants/{id}/schedules', 'restaurantScheduleStore');
            Route::patch('/restaurants/{id}/schedules/{date}', 'restaurantScheduleUpdate');
            Route::delete('/restaurants/{id}/schedules/{date}', 'restaurantScheduleDestroy');
            Route::get('/restaurants/{id}/admins', 'allAdmins');
            Route::post('/restaurants/{id}/admins', 'storeAdmin');
            Route::delete('/restaurants/{restaurant_id}/admins/{user_id}', 'destroyAdmin');
            Route::get('/restaurants/{id}/blocked-users', 'blockedUsers');
            Route::post('/restaurants/{id}/blocked-users', 'addBlockedUser');
            Route::delete('/restaurants/{restaurant_id}/blocked-users/{user_id}', 'deleteBlockedUser');
        });

        Route::controller(TableController::class)->group(function () {
            Route::get('/restaurants/{id}/tables', 'index');
            Route::get('/tables/{id}', 'show');
            Route::post('/tables', 'store');
            Route::patch('/tables/{id}', 'update');
            Route::delete('/tables/{id}', 'destroy');
        });

        Route::controller(ReminderTypeController::class)->group(function () {
            Route::get('/reminder_types', 'index')->can('viewAny', ReminderType::class);
            Route::get('/reminder_types/{id}', 'show')->can('view', ReminderType::class);
            Route::patch('/reminder_types/{id}', 'update')->can('update', ReminderType::class);
            Route::delete('/reminder_types/{id}', 'destroy')->can('delete', ReminderType::class);
            Route::post('/reminder_types', 'store')->can('create', ReminderType::class);
        });

        Route::controller(ReservationStatuseController::class)->group(function () {
            Route::get('/reservation_statuses', 'index')->can('viewAny', ReservationStatuse::class);
            Route::get('/reservation_statuses/{id}', 'show')->can('view', ReservationStatuse::class);
            Route::patch('/reservation_statuses/{id}', 'update')->can('update', ReservationStatuse::class);
            Route::delete('/reservation_statuses/{id}', 'destroy')->can('delete', ReservationStatuse::class);
            Route::post('/reservation_statuses', 'store')->can('create', ReservationStatuse::class);
        });

        Route::controller(ReviewController::class)->group(function () {
            Route::post('/restaurants/{id}/reviews', 'store')->can('create', Review::class);
            Route::patch('/reviews/{id}', 'update');
            Route::delete('/reviews/{id}', 'destroy');
        });
    });

    Route::controller(ReviewController::class)->group(function () {
        Route::get('/restaurants/{id}/reviews', 'index');
        Route::get('/reviews/{id}', 'show');
    });

    Route::post('/auth/email/change/confirm', [AuthController::class, 'changeEmail']);

    Route::controller(ChainController::class)->group(function () {
        Route::get('/chains', 'index');
        Route::get('/chains/{id}', 'show');
    });

    Route::controller(RestaurantController::class)->group(function () {
        Route::get('/restaurants', 'index');
        Route::get('/restaurants/{id}', 'show');
    });
});
