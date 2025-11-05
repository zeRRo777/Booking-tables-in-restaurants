<?php

namespace App\Rules;

use Carbon\Carbon;
use Closure;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;

class MinReservationTime implements ValidationRule, DataAwareRule
{
    protected array $data = [];

    public function setData(array $data): static
    {
        $this->data = $data;
        return $this;
    }

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (empty($this->data['starts_at'])) return;

        try {
            $starts = Carbon::createFromFormat('d.m.Y H:i', $this->data['starts_at']);
            $ends = Carbon::createFromFormat('d.m.Y H:i', $value);
            if ($starts->diffInMinutes($ends) < 60) {
                $fail('Минимальная продолжительность бронирования - 1 час.');
            }
        } catch (\Exception $e) {
        }
    }
}
