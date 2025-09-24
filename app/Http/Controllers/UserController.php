<?php

namespace App\Http\Controllers;

use App\Http\Requests\User\StoreUserRequest;
use App\Http\Resources\UserResource;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;


class UserController extends Controller
{
    public function __construct(
        protected UserService $userService
    ) {}
}
