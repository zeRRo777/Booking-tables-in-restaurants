<?php

namespace App\Http\Requests\ReminderType;

use App\DTOs\ReminderType\UpdateReminderTypeDTO;
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
        $id = $this->route('id');
        return [
            'name' => ['sometimes', 'string', 'max:50', Rule::unique('reminder_types', 'name')->ignore($id)],
            'minutes_before' => ['sometimes', 'integer', 'min:1'],
            'is_default' => ['sometimes', 'in:true,false'],
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

    public function toDto(): UpdateReminderTypeDTO
    {
        return UpdateReminderTypeDTO::from($this->validated());
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
