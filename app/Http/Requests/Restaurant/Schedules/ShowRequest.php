<?php

namespace App\Http\Requests\Restaurant\Schedules;

use App\DTOs\Restaurant\RestaurantScheduleShowDTO;
use Illuminate\Foundation\Http\FormRequest;

class ShowRequest extends FormRequest
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
            'date' => ['required', 'date_format:Y-m-d'],
            'id' => ['required', 'integer'],
        ];
    }

    public function attributes(): array
    {
        return [
            'date' => 'Дата',
            'id' => 'ID',
        ];
    }

    public function prepareForValidation(): void
    {
        $this->merge([
            'date' => $this->route('date'),
            'id' => $this->route('id'),
        ]);
    }

    public function toDto(): RestaurantScheduleShowDTO
    {
        return RestaurantScheduleShowDTO::from($this->validated());
    }
}
