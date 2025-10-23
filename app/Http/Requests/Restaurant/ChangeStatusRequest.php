<?php

namespace App\Http\Requests\Restaurant;

use App\DTOs\Restaurant\ChangeStatusDTO;
use Illuminate\Foundation\Http\FormRequest;

class ChangeStatusRequest extends FormRequest
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
            'status' => ['required', 'string', 'max:50', 'exists:restaurant_statuses,name'],
        ];
    }

    public function attributes(): array
    {
        return [
            'status' => 'Статус',
        ];
    }

    public function toDto(): ChangeStatusDTO
    {
        return ChangeStatusDTO::from($this->validated());
    }
}
