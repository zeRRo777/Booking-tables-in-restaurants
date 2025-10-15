<?php

namespace App\Http\Requests\User;

use App\DTOs\UserFilterDTO;
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
            'name' => ['sometimes', 'string', 'max:100'],
            'email' => ['sometimes', 'string', 'max:50'],
            'phone' => ['sometimes', 'string', 'max:20'],
            'is_blocked' => ['sometimes', 'in:true,false'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100']
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'Имя',
            'email' => 'Почта',
            'phone' => 'Телефон',
            'is_blocked' => 'Заблокирован',
            'per_page' => 'Количество пользователей на странице'
        ];
    }

    public function toDto(): UserFilterDTO
    {
        return UserFilterDTO::from($this->validated());
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
