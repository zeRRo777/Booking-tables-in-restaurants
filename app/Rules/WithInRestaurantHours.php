<?php

namespace App\Rules;

use App\Models\Restaurant;
use App\Models\RestaurantSchedule;
use Carbon\Carbon;
use Closure;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;

class WithInRestaurantHours implements ValidationRule, DataAwareRule
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
        $restaurantId = $this->data['restaurant_id'] ?? null;
        $endsAtStr = $this->data['ends_at'] ?? null;

        if (!$restaurantId || !$endsAtStr) {
            return;
        }

        $restaurant = Restaurant::find($restaurantId);
        if (!$restaurant) {
            return;
        }

        try {
            $startsAt = Carbon::createFromFormat('d.m.Y H:i', $value);
            $endsAt = Carbon::createFromFormat('d.m.Y H:i', $endsAtStr);
        } catch (\Exception $e) {
            return;
        }

        $schedule = RestaurantSchedule::where('restaurant_id', $restaurantId)
            ->where('date', $startsAt->toDateString())
            ->first();

        if ($schedule) {
            if ($schedule->is_closed) {
                $fail("Ресторан закрыт в этот день ({$startsAt->format('d.m.Y')}).");
                return;
            }
            $opensAtTime = $schedule->opens_at;
            $closesAtTime = $schedule->closes_at;
        } else {
            if ($startsAt->isWeekend()) {
                $opensAtTime = $restaurant->weekend_opens_at;
                $closesAtTime = $restaurant->weekend_closes_at;
            } else {
                $opensAtTime = $restaurant->weekdays_opens_at;
                $closesAtTime = $restaurant->weekdays_closes_at;
            }
        }

        if (!$opensAtTime || !$closesAtTime) {
            $fail("Для ресторана не установлено время работы на этот день.");
        }

        $openingDateTime = $startsAt->copy()->setTimeFrom($opensAtTime);
        $closingDateTime = $startsAt->copy()->setTimeFrom($closesAtTime);

        if ($closingDateTime->lt($openingDateTime)) {
            $closingDateTime->addDay();
        }

        if ($startsAt->lt($openingDateTime) || $endsAt->gt($closingDateTime)) {
            $fail("Бронирование возможно только в часы работы ресторана: с {$opensAtTime->format('H:i')} до {$closesAtTime->format('H:i')}.");
        }
    }
}
