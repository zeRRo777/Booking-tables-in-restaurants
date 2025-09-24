<?php

namespace App\Services;

use App\DTOs\CreateUserTokenDTO;
use App\Models\User;
use App\Models\UserToken;
use App\Repositories\Contracts\UserRepositoryInterface;
use Carbon\Carbon;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthService
{
    public function __construct(
        protected UserRepositoryInterface $userRepository
    ) {}

    public function login(User $user): UserToken
    {
        $token = JWTAuth::fromUser($user);

        $payload = JWTAuth::setToken($token)->getPayload();

        $expiresAt = Carbon::createFromTimestamp($payload['exp']);

        $userTokenDTO = new CreateUserTokenDTO($user->id, $token, $expiresAt);

        $token = $this->userRepository->createToken($userTokenDTO);

        return $token;
    }
}
