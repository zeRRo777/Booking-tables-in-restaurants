<?php

namespace App\Http\Requests\Auth;

use App\DTOs\User\CreateUserDTO;
use Illuminate\Foundation\Http\FormRequest;

class RegisterUserRequest extends FormRequest
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
            'email' => ['required', 'email', 'unique:users,email', 'max:50'],
            'password' => ['required', 'min:8', 'confirmed'],
            'name' => ['required', 'max:100', 'string'],
            'phone' => ['required', 'max:12', 'regex:/^\+\d{11}$/', 'unique:users,phone'],
        ];
    }

    public function toDTO(): CreateUserDTO
    {
        return CreateUserDTO::from($this->validated());
    }

    public function attributes(): array
    {
        return [
            'email' => 'Почта',
            'password' => 'Пароль',
            'name' => 'Имя',
            'phone' => 'Телефон'
        ];
    }
}
