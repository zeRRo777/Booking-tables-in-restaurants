<?php

namespace App\Http\Requests\ReminderType;

use App\DTOs\ReminderType\CreateReminderTypeDTO;
use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:50', 'unique:reminder_types,name'],
            'minutes_before' => ['required', 'integer', 'min:1'],
            'is_default' => ['nullable', 'in:true,false'],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'Название',
            'minutes_before' => 'Количество минут до',
            'is_default' => 'По умолчанию',
        ];
    }

    public function toDto(): CreateReminderTypeDTO
    {
        return CreateReminderTypeDTO::from($this->validated());
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

        if (array_key_exists('is_default', $validated)) {
            $value = $validated['is_default'];

            $validated['is_default'] = filter_var(
                $value,
                FILTER_VALIDATE_BOOLEAN,
                FILTER_NULL_ON_FAILURE
            );
        }

        return $validated;
    }
}
