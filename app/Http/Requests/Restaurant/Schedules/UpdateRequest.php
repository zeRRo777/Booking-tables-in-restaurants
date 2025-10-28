<?php

namespace App\Http\Requests\Restaurant\Schedules;

use App\DTOs\Restaurant\UpdateRestaurantScheduleDTO;
use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends FormRequest
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
        return [
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
            'restaurant_id' => ['required', 'integer'],
            'date' => ['required', 'date_format:Y-m-d'],
        ];
    }

    public function attributes(): array
    {
        return [
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
            'date' => $this->route('date'),
        ]);
    }

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

    public function toDto(): UpdateRestaurantScheduleDTO
    {
        return UpdateRestaurantScheduleDTO::from($this->validated());
    }
}
