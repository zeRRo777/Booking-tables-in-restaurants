<?php

namespace App\Services;

use App\DTOs\CreateUserTokenDTO;
use App\Models\User;
use App\Models\UserToken;
use App\Repositories\Contracts\UserRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthService
{
    public function __construct(
        protected UserRepositoryInterface $userRepository
    ) {}

    public function createAndSaveToken(User $user): UserToken
    {
        $token = JWTAuth::fromUser($user);

        $payload = JWTAuth::setToken($token)->getPayload();

        $expiresAt = Carbon::createFromTimestamp($payload['exp']);

        $userTokenDTO = new CreateUserTokenDTO($user->id, $token, $expiresAt);

        return $this->userRepository->createToken($userTokenDTO);
    }

    public function authenticate(string $email, string $password): ?User
    {
        $user = $this->userRepository->findByEmail($email);

        if ($user && Hash::check($password, $user->password)) {
            return $user;
        }

        return null;
    }

    public function logout(): void
    {
        $token = JWTAuth::getToken();

        if ($token) {
            $this->userRepository->deleteToken($token);

            JWTAuth::invalidate($token);
        }
    }
}
