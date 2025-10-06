<?php

namespace App\Repositories\Eloquent;

use App\Repositories\Contracts\PasswordResetRepositoryInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class EloquentPasswordResetRepository implements PasswordResetRepositoryInterface
{
    protected string $table = 'password_reset_tokens';

    public function createOrUpdate(string $email, string $hashedToken): bool
    {
        return DB::table($this->table)->updateOrInsert(
            ['email' => $email],
            [
                'token' => $hashedToken,
                'created_at' => Carbon::now(),
            ]
        );
    }

    public function findByEmail(string $email): object|null
    {
        return DB::table($this->table)->where('email', $email)->first();
    }

    public function deleteByEmail(string $email): bool
    {
        return DB::table($this->table)->where('email', $email)->delete() > 0;
    }
}
