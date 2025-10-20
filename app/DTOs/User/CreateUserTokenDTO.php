<?php

namespace App\DTOs\User;

use Carbon\Carbon;
use Spatie\LaravelData\Data;

class CreateUserTokenDTO extends Data
{
    public function __construct(
        public int $user_id,
        public string $token,
        public Carbon $expires_at
    ) {}
}
