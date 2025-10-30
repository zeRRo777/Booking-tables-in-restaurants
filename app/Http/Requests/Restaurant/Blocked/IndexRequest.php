<?php

namespace App\Http\Requests\Restaurant\Blocked;

use App\DTOs\Restaurant\BlockedUserFilterDTO;
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
            'name' => ['sometimes', 'string', 'max:100'],
            'email' => ['sometimes', 'string', 'max:50'],
            'phone' => ['sometimes', 'string', 'max:20'],
            'restaurant_id' => ['required', 'integer', 'exists:restaurants,id'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'sort_by' => ['sometimes', 'string', Rule::in(['id', 'name', 'email', 'phone', 'created_at'])],
            'sort_direction' => ['sometimes', 'string', Rule::in(['asc', 'desc'])],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'Имя',
            'email' => 'Почта',
            'phone' => 'Телефон',
            'per_page' => 'Количество пользователей на странице',
            'sort_by' => 'Поле сортировки',
            'sort_direction' => 'Направление сортировки',
        ];
    }

    public function toDto(): BlockedUserFilterDTO
    {
        return BlockedUserFilterDTO::from($this->validated());
    }

    public function prepareForValidation(): void
    {
        $this->merge([
            'restaurant_id' => $this->route('id'),
        ]);
    }
}
