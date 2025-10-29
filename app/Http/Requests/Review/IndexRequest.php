<?php

namespace App\Http\Requests\Review;

use App\DTOs\Review\ReviewFilterDTO;
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
            'rating' => ['sometimes', 'integer', 'min:1', 'max:5'],
            'sort_by' => ['sometimes', 'string', Rule::in(['created_at', 'id', 'rating'])],
            'sort_direction' => ['sometimes', 'string', Rule::in(['asc', 'desc'])],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'restaurant_id' => ['required', 'integer', 'exists:restaurants,id'],
        ];
    }

    public function attributes(): array
    {
        return [
            'rating' => 'Рейтинг',
            'sort_by' => 'Поле сортировки',
            'sort_direction' => 'Направление сортировки',
            'per_page' => 'Количество записей на странице',
            'restaurant_id' => 'Ресторан',
        ];
    }

    public function prepareForValidation(): void
    {
        $this->merge([
            'restaurant_id' => $this->route('id'),
        ]);
    }

    public function toDto(): ReviewFilterDTO
    {
        return ReviewFilterDTO::from($this->validated());
    }
}
