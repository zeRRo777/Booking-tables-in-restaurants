<?php

namespace App\Rules;

use App\Models\ReservationStatuse;
use Carbon\Carbon;
use Closure;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\DB;

class TableIsAvailable implements ValidationRule, DataAwareRule
{
    protected ?int $reservationIdToIgnore;
    protected array $data = [];

    public function __construct(?int $reservationIdToIgnore)
    {
        $this->reservationIdToIgnore = $reservationIdToIgnore;
    }

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
        if (empty($this->data['starts_at']) || empty($this->data['ends_at'])) {
            return;
        }

        try {
            $startsAt = Carbon::createFromFormat('d.m.Y H:i', $this->data['starts_at']);
            $endsAt = Carbon::createFromFormat('d.m.Y H:i', $this->data['ends_at']);
        } catch (\Exception $e) {
            return;
        }

        $cancelledStatusId = ReservationStatuse::where('name', 'Cancelled')->value('id');

        $query = DB::table('reservations')
            ->where('table_id', $value)
            ->where('id', '!=', $this->reservationIdToIgnore)
            ->where(function ($q) use ($startsAt, $endsAt) {
                $q->where('starts_at', '<', $endsAt)->where('ends_at', '>', $startsAt);
            });

        if ($cancelledStatusId) {
            $query->where('status_id', '!=', $cancelledStatusId);
        }

        $isBooked = $query->exists();

        if ($isBooked) {
            $fail('Выбранный стол уже забронирован на это время.');
        }
    }
}
