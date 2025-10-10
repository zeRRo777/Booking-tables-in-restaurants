<?php

namespace App\Services;

use App\DTOs\CreateUserTokenDTO;
use App\Exceptions\AuthenticateException;
use App\Exceptions\MissingEmailChangeException;
use App\Exceptions\MissingPasswordResetTokenException;
use App\Exceptions\MissingPhoneChangeCodeException;
use App\Exceptions\TokenExpiredException;
use App\Exceptions\UserNotFoundException;
use App\Models\User;
use App\Models\UserToken;
use App\Notifications\EmailChangeNewEmailNotification;
use App\Notifications\EmailChangeOldEmailNotification;
use App\Notifications\PasswordResetNofication;
use App\Notifications\SuccessChangeEmailNotification;
use App\Notifications\SuccessChangePasswordNotification;
use App\Notifications\SuccessChangePhoneNotification;
use App\Repositories\Contracts\EmailChangeRepositoryInterface;
use App\Repositories\Contracts\PasswordResetRepositoryInterface;
use App\Repositories\Contracts\PhoneChangeRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Repositories\Contracts\UserTokensRepositoryInterface;
use Carbon\Carbon;
use Exception;
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
        protected EmailChangeRepositoryInterface $emailChangeRepository,
        protected UserTokensRepositoryInterface $userTokensRepository,
        protected PhoneChangeRepositoryInterface $phoneChangeRepository
    ) {}

    public function createAndSaveToken(User $user): UserToken
    {
        $token = JWTAuth::fromUser($user);

        $payload = JWTAuth::setToken($token)->getPayload();

        $expiresAt = Carbon::createFromTimestamp($payload['exp']);

        $userTokenDTO = new CreateUserTokenDTO($user->id, $token, $expiresAt);

        return $this->userRepository->createToken($userTokenDTO);
    }

    public function authenticate(string $email, string $password): User
    {
        $user = $this->userRepository->findByEmail($email);

        if ($user && Hash::check($password, $user->password)) {
            return $user;
        }

        throw new AuthenticateException('Неверный логин или пароль!');
    }

    public function logout(): void
    {
        $token = JWTAuth::getToken();

        if (!$token) {
            throw new AuthenticateException('Токен для выхода из аккаунта не найден!', 401);
        }

        $this->userRepository->deleteToken($token);

        JWTAuth::invalidate($token);
    }

    public function changePassword(User $user, string $password, bool $shouldNotify = false): void
    {
        DB::transaction(function () use ($user, $password): void {
            $this->userRepository->update($user, ['password' => Hash::make($password)]);
            $this->invalidateAllUserTokens($user);
        });

        if ($shouldNotify) {
            $user->notify(new SuccessChangePasswordNotification());
        }
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

    public function resetPassword(string $email, string $token, string $password): void
    {
        $resetRecord = $this->passwordResetRepository->findByEmail($email);

        if (!$resetRecord) {
            throw new MissingPasswordResetTokenException('Токен для сброса пароля не найден!');
        }

        if (!Hash::check($token, $resetRecord->token)) {
            throw new AuthenticateException('Неверный токен для сброса пароля!');
        }

        $expiresInMinutes = config('auth.passwords.users.expire', 60);

        if (Carbon::parse($resetRecord->created_at)->addMinutes($expiresInMinutes)->isPast()) {
            $this->passwordResetRepository->deleteByEmail($email);
            throw new TokenExpiredException('Токен для сброса пароля устарел!');
        }

        $user = $this->userRepository->findByEmail($email);

        if (!$user) {
            throw new UserNotFoundException('Пользователь не найден!');
        }

        DB::transaction(function () use ($user, $password, $email) {
            $this->changePassword($user, $password, false);
            $this->passwordResetRepository->deleteByEmail($email);
        });

        $user->notify(new SuccessChangePasswordNotification());
    }

    public function sendChangeEmailLink(User $user, string $newEmail): void
    {
        $token = Str::random(64);
        $this->emailChangeRepository->createOrUpdate($user, $token, $newEmail);
        // $user->notify(new EmailChangeOldEmailNotification());
        $user->notify(new EmailChangeNewEmailNotification($token));
    }

    public function changeEmail(string $token): void
    {
        $tokenObject = $this->emailChangeRepository->findByToken($token);

        if (!$tokenObject) {
            throw new MissingEmailChangeException('Токен для смены email не найден!');
        }

        $user = $this->userRepository->findById($tokenObject->user_id);

        if (!$user) {
            throw new UserNotFoundException('Пользователь не найден!');
        }

        $expiresInMinutes = config('auth.email_change_expiration', 60);

        if (Carbon::parse($tokenObject->created_at)->addMinutes($expiresInMinutes)->isPast()) {
            $this->emailChangeRepository->deleteByUser($user);
            throw new TokenExpiredException('Токен для смены email устарел!');
        }

        DB::transaction(function () use ($user, $tokenObject): void {
            $this->userRepository->update($user, [
                'email' => $tokenObject->new_email,
                'email_verified_at' => Carbon::now(),
            ]);
            $this->emailChangeRepository->deleteByUser($user);
            $this->invalidateAllUserTokens($user);
        });

        $user->notify(new SuccessChangeEmailNotification());
    }

    public function invalidateAllUserTokens(User $user): void
    {
        $tokens = $this->userTokensRepository->getUserTokens($user);

        $this->userTokensRepository->deleteUserTokens($user);

        foreach ($tokens as $token) {
            try {
                JWTAuth::invalidate($token);
            } catch (Exception $e) {
                Log::warning('JWT Token invalidation failed for user ' . $user->id . ': ' . $e->getMessage());
            }
        }
    }

    public function sendChangePhoneCode(User $user, string $phone): void
    {
        $code = (string) random_int(100000, 999999);

        $this->phoneChangeRepository->createOrUpdate(
            $user,
            Hash::make($code),
            $phone
        );

        //sms с кодом на телефон
    }

    public function changePhone(User $user, string $code): void
    {
        $codeObject = $this->phoneChangeRepository->findByUser($user);

        if (!$codeObject) {
            throw new MissingPhoneChangeCodeException('Не найден код для смены телефона!');
        }

        if (!Hash::check($code, $codeObject->code)) {
            throw new AuthenticateException('Неверный код для смены телефона!');
        }

        $expiresInMinutes = config('auth.email_change_expiration', 10);

        if (Carbon::parse($codeObject->created_at)->addMinutes($expiresInMinutes)->isPast()) {
            $this->phoneChangeRepository->deleteByUser($user);
            throw new TokenExpiredException('Токен для смены телефона устарел!');
        }

        DB::transaction(function () use ($user, $codeObject): void {
            $this->userRepository->update($user, [
                'phone' => $codeObject->new_phone,
                'phone_verified_at' => Carbon::now(),
            ]);
            $this->phoneChangeRepository->deleteByUser($user);
        });

        $user->notify(new SuccessChangePhoneNotification());
    }
}
