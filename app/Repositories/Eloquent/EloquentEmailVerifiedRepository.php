<?php

namespace App\Repositories\Eloquent;

use App\Repositories\Contracts\EmailVefiedRepositoryInterface;
use Illuminate\Support\Facades\DB;

class EloquentEmailVerifiedRepository implements EmailVefiedRepositoryInterface
{

    protected string $table = 'email_verification_codes';

    public function createOrUpdate($email, $hashedCode): bool
    {
        return DB::table($this->table)->updateOrInsert(
            ['email' => $email],
            ['code' => $hashedCode]
        );
    }

    public function findByEmail(string $email): object|null
    {
        return DB::table($this->table)->where('email', $email)->first();
    }

    public function deleteByEmail(string $email): int
    {
        return DB::table($this->table)->where('email', $email)->delete();
    }
}
