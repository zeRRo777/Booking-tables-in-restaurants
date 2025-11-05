<?php

namespace App\Rules;

use Carbon\Carbon;
use Closure;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\DB;

class DailyReservationLimit implements ValidationRule, DataAwareRule
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
        $currentUser = request()->user();

        $restaurantId = $this->data['restaurant_id'] ?? null;

        $isSuperAdmin = $currentUser->hasRole('superadmin');

        $isRestaurantAdmin = $currentUser->hasRole('restaurant_admin') &&
            $currentUser->administeredRestaurants()->where('restaurant_id', $restaurantId)->exists();

        if ($isSuperAdmin || $isRestaurantAdmin) {
            return;
        }

        try {
            $startsAt = Carbon::createFromFormat('d.m.Y H:i', $this->data['starts_at']);
        } catch (\Exception $e) {
            return;
        }

        $reservationsCount = DB::table('reservations')
            ->where('user_id', $value)
            ->whereDate('starts_at', $startsAt->toDateString())
            ->count();

        if ($reservationsCount >= 5) {
            $fail('Вы достигли лимита бронирований на этот день (5).');
        }
    }
}
