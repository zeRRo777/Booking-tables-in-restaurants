<?php

namespace App\Http\Requests\Restaurant\Schedules;

use App\DTOs\Restaurant\CreateRestaurantScheduleDTO;
use Carbon\Carbon;
use Exception;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRequest extends FormRequest
{
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
        $restaurantId = $this->route('id');

        return [
            'date' => [
                'required',
                'string',
                'date_format:Y-m-d',
                Rule::unique('restaurant_schedules')->where(function ($query) use ($restaurantId) {
                    return $query->where('restaurant_id', $restaurantId);
                }),
            ],
            'opens_at' => [
                'nullable',
                'required_with:closes_at',
                'string',
                'date_format:H:i',
            ],
            'closes_at' => [
                'nullable',
                'required_with:opens_at',
                'string',
                'date_format:H:i',
                'different:opens_at',
            ],
            'is_closed' => ['nullable', 'string', 'in:true,false'],
            'description' => ['nullable', 'string', 'max:255'],
            'restaurant_id' => ['required', 'integer', 'exists:restaurants,id'],
        ];
    }

    public function attributes(): array
    {
        return [
            'date' => 'Дата',
            'opens_at' => 'Время открытия',
            'closes_at' => 'Время закрытия',
            'is_closed' => 'Закрыто',
            'description' => 'Описание',
        ];
    }

    public function prepareForValidation(): void
    {
        $this->merge([
            'restaurant_id' => $this->route('id'),
        ]);

        if ($this->has('date')) {
            try {
                $formatDate = Carbon::createFromFormat('d.m.Y', $this->input('date'))->format('Y-m-d');

                $this->merge([
                    'date' => $formatDate,
                ]);
            } catch (Exception $e) {
            }
        }
    }

    public function toDto(): CreateRestaurantScheduleDTO
    {
        return CreateRestaurantScheduleDTO::from($this->validated());
    }

    /**
     * Get the validated data from the request.
     * @param string|null $key
     * @param mixed $default
     * @return array<string, mixed>
     */
    public function validated($key = null, $default = null): array
    {
        $validated = parent::validated();

        if (array_key_exists('is_closed', $validated)) {
            $value = $validated['is_closed'];

            $validated['is_closed'] = filter_var(
                $value,
                FILTER_VALIDATE_BOOLEAN,
                FILTER_NULL_ON_FAILURE
            );
        }

        return $validated;
    }
}
