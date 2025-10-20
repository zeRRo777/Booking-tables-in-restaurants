<?php

namespace App\Http\Requests\User;

use App\DTOs\User\UpdateMeDTO;
use Illuminate\Foundation\Http\FormRequest;

class UpdateMeRequest extends FormRequest
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
            'name' => ['nullable', 'max:100', 'string'],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'Имя'
        ];
    }

    public function toDto(): UpdateMeDTO
    {
        return UpdateMeDTO::from($this->validated());
    }
}
