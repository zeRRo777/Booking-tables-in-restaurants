<?php

namespace App\Repositories\Eloquent;

use App\Models\User;
use App\Repositories\Contracts\PhoneChangeRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class EloquentPhoneChangeRepository implements PhoneChangeRepositoryInterface
{
    protected string $table = 'phone_change_tokens';
    public function createOrUpdate(User $user, string $hashedCode, string $phone): bool
    {
        return DB::table($this->table)->updateOrInsert(
            ['user_id' => $user->id],
            [
                'code' => $hashedCode,
                'new_phone' => $phone,
                'created_at' => Carbon::now(),
            ]
        );
    }

    public function findByUser(User $user): object|null
    {
        return DB::table($this->table)->where('user_id', $user->id)->first();
    }

    public function deleteByUser(User $user): int
    {
        return DB::table($this->table)->where('user_id', $user->id)->delete();
    }
}
