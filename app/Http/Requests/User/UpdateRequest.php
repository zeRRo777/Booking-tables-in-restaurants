<?php

namespace App\Http\Requests\User;

use App\DTOs\UpdateUserDTO;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRequest extends FormRequest
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
            'email' => ['nullable', 'email', 'max:50', Rule::unique('users')->ignore($this->route('id'))],
            'name' => ['nullable', 'max:100', 'string'],
            'phone' => ['nullable', 'max:12', 'string', 'regex:/^\+\d{11}$/', Rule::unique('users')->ignore($this->route('id'))],
            'is_blocked' => ['nullable', 'in:true,false'],
        ];
    }

    public function attributes(): array
    {
        return [
            'email' => 'Почта',
            'name' => 'Имя',
            'phone' => 'Телефон',
            'is_blocked' => 'Заблокирован',
        ];
    }

    public function toDto(): UpdateUserDTO
    {
        return UpdateUserDTO::from($this->validated());
    }

    /**
     * Get the validated data from the request.
     * @param string|null $key
     * @param mixed $default
     * @return array<string, mixed>
     */
    public function validated($key = null, $default = null): array
    {
        $validated = parent::validated();

        if (array_key_exists('is_blocked', $validated)) {
            $value = $validated['is_blocked'];

            $validated['is_blocked'] = filter_var(
                $value,
                FILTER_VALIDATE_BOOLEAN,
                FILTER_NULL_ON_FAILURE
            );
        }

        return $validated;
    }
}
