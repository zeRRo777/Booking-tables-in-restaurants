<?php

namespace App\Services;

use App\DTOs\CreateUserTokenDTO;
use App\Models\User;
use App\Models\UserToken;
use App\Notifications\EmailChangeNewEmailNotification;
use App\Notifications\EmailChangeOldEmailNotification;
use App\Notifications\PasswordResetNofication;
use App\Repositories\Contracts\EmailChangeRepositoryInterface;
use App\Repositories\Contracts\PasswordResetRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use Carbon\Carbon;
use Exception;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthService
{
    public function __construct(
        protected UserRepositoryInterface $userRepository,
        protected PasswordResetRepositoryInterface $passwordResetRepository,
        protected EmailChangeRepositoryInterface $emailChangeRepository
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

    public function changePassword(User $user, string $password): void
    {
        $tokens = $user->tokens()->pluck('token');

        DB::transaction(function () use ($user, $password, $tokens): void {
            $this->userRepository->update($user, ['password' => Hash::make($password)]);

            $user->tokens()->delete();

            foreach ($tokens as $token) {
                try {
                    JWTAuth::invalidate($token);
                } catch (Exception $e) {
                    Log::warning('JWT Token invalidation failed after password change: ' . $e->getMessage());
                }
            }
        });
    }

    public function sendResetLink(string $email): void
    {
        $user = $this->userRepository->findByEmail($email);

        if ($user) {
            $token = Str::random(64);

            $this->passwordResetRepository->createOrUpdate($email, Hash::make($token));

            $user->notify(new PasswordResetNofication($token));
        }
    }

    public function resetPassword(string $email, string $token, string $password): bool
    {
        $resetRecord = $this->passwordResetRepository->findByEmail($email);

        if (!$resetRecord) {
            return false;
        }

        if (!Hash::check($token, $resetRecord->token)) {
            return false;
        }

        $expiresInMinutes = config('auth.passwords.users.expire', 60);

        if (Carbon::parse($resetRecord->created_at)->addMinutes($expiresInMinutes)->isPast()) {
            $this->passwordResetRepository->deleteByEmail($email);
            return false;
        }

        $user = $this->userRepository->findByEmail($email);

        if (!$user) {
            return false;
        }

        DB::transaction(function () use ($user, $password, $email) {
            $this->changePassword($user, $password);
            $this->passwordResetRepository->deleteByEmail($email);
        });

        return true;
    }

    public function sendChangeEmailLink(User $user, string $newEmail): void
    {
        $token = Str::random(64);
        $this->emailChangeRepository->createOrUpdate($user, Hash::make($token), $newEmail);
        // $user->notify(new EmailChangeOldEmailNotification());
        $user->notify(new EmailChangeNewEmailNotification($token));
    }
}
