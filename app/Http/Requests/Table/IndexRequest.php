<?php

namespace App\Http\Requests\Table;

use App\DTOs\Table\TableFilterDTO;
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
            'zone' => ['sometimes', 'string', 'max:50', 'exists:tables,zone'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'sort_by' => ['sometimes', 'string', Rule::in(['id', 'capacity_min', 'capacity_max'])],
            'sort_direction' => ['sometimes', 'string', Rule::in(['asc', 'desc'])],
        ];
    }

    public function attributes(): array
    {
        return [
            'zone' => 'Зона',
            'capacity_min' => 'Минимальная вместимость',
            'capacity_max' => 'Максимальная вместимость',
            'per_page' => 'Количество записей на странице',
            'sort_by' => 'Поле сортировки',
            'sort_direction' => 'Направление сортировки',
        ];
    }

    public function toDto(): TableFilterDTO
    {
        return TableFilterDTO::from($this->validated());
    }
}
