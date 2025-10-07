<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class PasswordResetConfirmRequest extends FormRequest
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
            'email' => ['required', 'string', 'email', 'max:50'],
            'password' => ['required', 'min:8', 'confirmed'],
            'token' => ['required', 'string'],
        ];
    }

    public function attributes(): array
    {
        return [
            'email' => 'Почта',
            'password' => 'Пароль',
            'token' => 'Токен',
            'password_confirmation' => 'Подтверждение пароля',
        ];
    }
}
