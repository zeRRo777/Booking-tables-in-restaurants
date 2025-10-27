<?php

namespace App\Http\Requests\Restaurant\Schedules;

use App\DTOs\Restaurant\RestaurantScheduleFilterDTO;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexRequest extends FormRequest
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
            'date_start' => ['sometimes', 'date_format:d.m.Y'],
            'date_end' => ['sometimes', 'date_format:d.m.Y'],
            'sort_by' => ['sometimes', 'string', Rule::in(['date', 'created_at'])],
            'sort_direction' => ['sometimes', 'string', Rule::in(['asc', 'desc'])],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ];
    }

    public function attributes(): array
    {
        return [
            'date_start' => 'Дата начала',
            'date_end' => 'Дата окончания',
            'sort_by' => 'Поле сортировки',
            'sort_direction' => 'Направление сортировки',
            'per_page' => 'Количество записей на странице',
        ];
    }

    public function toDto(): RestaurantScheduleFilterDTO
    {
        return RestaurantScheduleFilterDTO::from($this->validated());
    }
}
