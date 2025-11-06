<?php

namespace App\Http\Requests\Reservation;

use App\DTOs\Reservation\CreateReservationDTO;
use App\Models\ReminderType;
use App\Models\RestaurantStatuse;
use App\Rules\DailyReservationLimit;
use App\Rules\MinReservationTime;
use App\Rules\TableCapacity;
use App\Rules\TableIsAvailable;
use App\Rules\WithInRestaurantHours;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRequest extends FormRequest
{

    protected $stopOnFirstFailure = true;

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
        $status = RestaurantStatuse::where('name', 'active')->firstOrFail();

        return [
            'special_wish' => [
                'nullable',
                'string',
                'max:500'
            ],
            'starts_at' => [
                'required',
                'date_format:d.m.Y H:i',
                'after:now',
                new WithInRestaurantHours(),
            ],
            'ends_at' => [
                'required',
                'date_format:d.m.Y H:i',
                new MinReservationTime()
            ],
            'user_id' => [
                'required',
                'integer',
                'exists:users,id',
                new DailyReservationLimit(),
            ],
            'restaurant_id' => [
                'required',
                'integer',
                Rule::exists('restaurants', 'id')->where('status_id', $status->id)
            ],
            'count_people' => [
                'required',
                'integer',
                'min:1'
            ],
            'reminder_type_id' => [
                'required',
                'string',
                'exists:reminder_types,name'
            ],
            'table_id' => [
                'required',
                'integer',
                Rule::exists('tables', 'id')->where('restaurant_id', $this->input('restaurant_id')),
                new TableCapacity(),
                new TableIsAvailable(null),
            ],
        ];
    }

    public function attributes(): array
    {
        return [
            'special_wish' => 'Особое пожелание',
            'starts_at' => 'Время начала',
            'ends_at' => 'Время окончания',
            'user_id' => 'Пользователь',
            'restaurant_id' => 'Ресторан',
            'count_people' => 'Количество людей',
            'reminder_type_id' => 'Тип напоминания',
            'table_id' => 'Стол',
        ];
    }

    public function toDto(): CreateReservationDTO
    {
        $validatedData = $this->validated();

        $reminderTypeName = $validatedData['reminder_type_id'];
        $validatedData['reminder_type_id'] = ReminderType::where('name', $reminderTypeName)->firstOrFail()->id;

        return CreateReservationDTO::from($validatedData);
    }

    protected function prepareForValidation(): void
    {
        $user = $this->user();

        $restaurantId = $this->input('restaurant_id');

        $canCreateForOthers = $user->hasRole('superadmin') ||
            ($user->hasRole('admin_restaurant')
                && $user->administeredRestaurants()
                ->where('restaurant_id', $restaurantId)
                ->exists()
            );

        if (!$this->has('user_id') || !$canCreateForOthers) {
            $this->merge([
                'user_id' => $user->id,
            ]);
        }
    }
}
