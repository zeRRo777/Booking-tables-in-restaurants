<?php

namespace App\Rules;

use App\Models\User;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class UserHaveRole implements ValidationRule
{
    public function __construct(protected int $userId) {}
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $user = User::find($this->userId);

        if ($user && $user->hasRole($value)) {
            $fail('Пользвоатель уже имеет роль :value.');
        }
    }
}
