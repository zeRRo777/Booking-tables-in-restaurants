<?php

namespace App\Repositories\Eloquent;

use App\Repositories\Contracts\PhoneVefiedRepositoryInterface;
use Illuminate\Support\Facades\DB;

class EloquentPhoneVerifiedRepository implements PhoneVefiedRepositoryInterface
{

    protected string $table = 'phone_verification_codes';

    public function createOrUpdate($phone, $hashedCode): bool
    {
        return DB::table($this->table)->updateOrInsert(
            ['phone' => $phone],
            ['code' => $hashedCode]
        );
    }

    public function findByPhone(string $phone): object|null
    {
        return DB::table($this->table)->where('phone', $phone)->first();
    }

    public function deleteByPhone(string $phone): int
    {
        return DB::table($this->table)->where('phone', $phone)->delete();
    }
}
