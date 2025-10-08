<?php

namespace App\Http\Requests\Auth;

use App\Rules\PasswordCheck;
use Illuminate\Foundation\Http\FormRequest;

class ChangeEmailUserRequest extends FormRequest
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
            'new_email' => ['required', 'email', 'max:50', 'unique:users,email'],
            'password' => ['required', 'string', new PasswordCheck()],
        ];
    }

    public function attributes(): array
    {
        return [
            'new_email' => 'Новая почта',
            'password' => 'Пароль',
        ];
    }
}
