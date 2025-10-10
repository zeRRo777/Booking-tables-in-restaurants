<?php

namespace App\Http\Requests\Auth;

use App\Rules\PasswordCheck;
use Illuminate\Foundation\Http\FormRequest;

class ChangePhoneUserRequest extends FormRequest
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
            'new_phone' => ['required', 'string', 'max:12', 'regex:/^\+\d{11}$/', 'unique:users,phone'],
            'password' => ['required', 'string', new PasswordCheck()],
        ];
    }

    public function attributes(): array
    {
        return [
            'new_phone' => 'Новый телефон',
            'password' => 'Пароль',
        ];
    }
}
