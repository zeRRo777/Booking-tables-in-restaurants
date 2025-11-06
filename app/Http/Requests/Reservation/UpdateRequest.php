<?php

namespace App\Http\Requests\Reservation;

use App\DTOs\Reservation\UpdateReservationDTO;
use App\DTOs\ReservationStatuse\UpdateReservationStatuseDTO;
use App\Models\ReminderType;
use App\Models\Reservation;
use App\Models\ReservationStatuse;
use App\Models\RestaurantStatuse;
use App\Rules\MinReservationTime;
use App\Rules\TableCapacity;
use App\Rules\TableIsAvailable;
use App\Rules\WithInRestaurantHours;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRequest extends FormRequest
{
    protected ?Reservation $reservation = null;

    protected function getReservation(): Reservation
    {
        if ($this->reservation === null) {
            $this->reservation = Reservation::findOrFail($this->route('id'));
        }
        return $this->reservation;
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $user = $this->user();
        $statusRule = 'prohibited';
        if ($user->hasRole('superadmin') || $user->hasRole('admin_restaurant')) {
            $statusRule = 'sometimes';
        }

        return [
            'special_wish' => [
                'sometimes',
                'string',
                'max:500',
            ],
            'starts_at' => [
                'sometimes',
                'date_format:d.m.Y H:i',
                'after:now',
                new WithInRestaurantHours(),
            ],
            'ends_at' => [
                'sometimes',
                'date_format:d.m.Y H:i',
                new MinReservationTime()
            ],
            'restaurant_id' => [],
            'count_people' => [
                'sometimes',
                'integer',
                'min:1'
            ],
            'reminder_type_id' => [
                'sometimes',
                'string',
                'exists:reminder_types,name'
            ],
            'table_id' => [
                'sometimes',
                'integer',
                Rule::exists('tables', 'id')->where('restaurant_id', $this->getReservation()->restaurant_id),
                new TableCapacity(),
                new TableIsAvailable($this->route('id')),
            ],
            'status_id' => [
                $statusRule,
                'string',
                'exists:reservation_statuses,name'
            ],
        ];
    }

    public function attributes(): array
    {
        return [
            'special_wish' => 'Особое пожелание',
            'starts_at' => 'Время начала',
            'ends_at' => 'Время окончания',
            'count_people' => 'Количество людей',
            'reminder_type_id' => 'Тип напоминания',
            'table_id' => 'Стол',
            'status_id' => 'Статус',
        ];
    }

    public function toDto(): UpdateReservationDTO
    {
        $validatedData = $this->validated();

        if (isset($validatedData['reminder_type_id'])) {
            $validatedData['reminder_type_id'] = ReminderType::where('name', $validatedData['reminder_type_id'])->value('id');
        }
        if (isset($validatedData['status_id'])) {
            $validatedData['status_id'] = ReservationStatuse::where('name', $validatedData['status_id'])->value('id');
        }

        return UpdateReservationDTO::from($validatedData);
    }

    public function prepareForValidation(): void
    {
        $reservation = $this->getReservation();

        $dataToMerge = [
            'restaurant_id' => $reservation->restaurant_id,
        ];

        if ($this->has('starts_at') && !$this->has('ends_at')) {
            $dataToMerge['ends_at'] = $reservation->ends_at->format('d.m.Y H:i');
        } elseif (!$this->has('starts_at') && $this->has('ends_at')) {
            $dataToMerge['starts_at'] = $reservation->starts_at->format('d.m.Y H:i');
        }

        if ($this->has('count_people') && !$this->has('table_id')) {
            $dataToMerge['table_id'] = $reservation->table_id;
        } elseif (!$this->has('count_people') && $this->has('table_id')) {
            $dataToMerge['count_people'] = $reservation->count_people;
        }

        $this->merge($dataToMerge);
    }
}
