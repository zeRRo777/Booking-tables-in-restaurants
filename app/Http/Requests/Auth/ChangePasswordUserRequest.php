<?php

namespace App\Http\Requests\Auth;

use App\Rules\PasswordCheck;
use Illuminate\Foundation\Http\FormRequest;

class ChangePasswordUserRequest extends FormRequest
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
            'old_password' => ['required', 'string', new PasswordCheck()],
            'password' => ['required', 'min:8', 'confirmed'],
        ];
    }

    public function attributes(): array
    {
        return [
            'old_password' => 'Старый пароль',
            'password' => 'Новый пароль',
            'password_confirmation' => 'Подтверждение пароля',
        ];
    }
}
