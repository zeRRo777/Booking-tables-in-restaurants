<?php

namespace App\Repositories\Eloquent;

use App\Models\User;
use App\Repositories\Contracts\EmailChangeRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

use function Laravel\Prompts\table;

class EloquentEmailChangeRepository implements EmailChangeRepositoryInterface
{

    protected string $table = 'email_change_tokens';

    public function createOrUpdate(User $user, string $token, string $email): bool
    {
        return DB::table($this->table)->updateOrInsert(
            ['user_id' => $user->id],
            [
                'token' => $token,
                'new_email' => $email,
                'created_at' => Carbon::now()
            ]
        );
    }

    public function deleteByUser(User $user): bool
    {
        return DB::table($this->table)->where('user_id', $user->id)->delete();
    }

    public function findByToken(string $token): object|null
    {
        return DB::table($this->table)->where('token', $token)->first();
    }
}
