<?php

namespace App\Http\Requests\ReminderType;

use App\DTOs\ReminderType\ReminderTypeFilterDTO;
use Illuminate\Foundation\Http\FormRequest;

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
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ];
    }

    public function attributes(): array
    {
        return [
            'per_page' => 'Количество записей на странице',
        ];
    }

    public function toDto(): ReminderTypeFilterDTO
    {
        return ReminderTypeFilterDTO::from($this->validated());
    }
}
