<?php

namespace App\Services;

use App\Exceptions\AuthenticateException;
use App\Exceptions\MissingEmailVerificationCodeException;
use App\Exceptions\MissingPhoneVerificationCodeException;
use App\Exceptions\TokenExpiredException;
use App\Exceptions\UserAlreadyVerfiedException;
use App\Models\User;
use App\Notifications\EmailVerifyNotification;
use App\Notifications\SuccessVerifyEmailNotification;
use App\Notifications\SuccessVerifyPhoneNotification;
use App\Repositories\Contracts\EmailVefiedRepositoryInterface;
use App\Repositories\Contracts\PhoneVefiedRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class VerifyService
{
    public function __construct(
        protected EmailVefiedRepositoryInterface $emailVefiedRepository,
        protected UserRepositoryInterface $userRepository,
        protected PhoneVefiedRepositoryInterface $phoneVefiedRepository
    ) {}

    public function sendVerifyEmail(User $user): void
    {
        if ($user->hasVerifiedEmail()) {
            throw new UserAlreadyVerfiedException('Пользователь уже верифицирован');
        }

        $code = (string) random_int(100000, 999999);

        $this->emailVefiedRepository->createOrUpdate(
            $user->email,
            Hash::make($code)
        );

        $user->notify(new EmailVerifyNotification($code));
    }

    public function verifyEmail(User $user, string $code): void
    {
        if ($user->hasVerifiedEmail()) {
            throw new UserAlreadyVerfiedException('Пользователь уже верифицирован');
        }

        $objectCode = $this->emailVefiedRepository->findByEmail($user->email);

        if (!$objectCode) {
            throw new MissingEmailVerificationCodeException('Не найден код для подтверждения верификации');
        }

        if (!Hash::check($code, $objectCode->code)) {
            throw new AuthenticateException('Неверный код для подтверждения верификации');
        }

        $expiresInMinutes = config('verify.email_verify_expiration', 10);

        if (Carbon::parse($objectCode->created_at)->addMinutes($expiresInMinutes)->isPast()) {
            $this->emailVefiedRepository->deleteByEmail($user->email);
            throw new TokenExpiredException('Срок дейсвия кода для подтверждения верификации истек');
        }

        DB::transaction(function () use ($user): void {
            $this->userRepository->update($user, [
                'email_verified_at' => Carbon::now(),
            ]);
            $this->emailVefiedRepository->deleteByEmail($user->email);
        });

        $user->notify(new SuccessVerifyEmailNotification());
    }

    public function sendVerifyPhone(User $user): void
    {
        if ($user->hasVerifiedPhone()) {
            throw new UserAlreadyVerfiedException('Пользователь уже верифицирован');
        }

        $code = (string) random_int(100000, 999999);

        $this->phoneVefiedRepository->createOrUpdate(
            $user->phone,
            Hash::make($code)
        );

        //уведолмение на телефон с кодом
    }

    public function verifyPhone(User $user, string $code): void
    {
        if ($user->hasVerifiedPhone()) {
            throw new UserAlreadyVerfiedException('Пользователь уже верифицирован');
        }

        $objectCode = $this->phoneVefiedRepository->findByPhone($user->phone);

        if (!$objectCode) {
            throw new MissingPhoneVerificationCodeException('Не найден код для подтверждения верификации');
        }

        if (!Hash::check($code, $objectCode->code)) {
            throw new AuthenticateException('Неверный код для подтверждения верификации');
        }

        $expiresInMinutes = config('verify.phone_verify_expiration', 10);

        if (Carbon::parse($objectCode->created_at)->addMinutes($expiresInMinutes)->isPast()) {
            $this->phoneVefiedRepository->deleteByPhone($user->phone);
            throw new TokenExpiredException('Срок дейсвия кода для подтверждения верификации истек');
        }

        DB::transaction(function () use ($user): void {
            $this->userRepository->update($user, [
                'phone_verified_at' => Carbon::now(),
            ]);
            $this->phoneVefiedRepository->deleteByPhone($user->phone);
        });

        $user->notify(new SuccessVerifyPhoneNotification());
    }
}
