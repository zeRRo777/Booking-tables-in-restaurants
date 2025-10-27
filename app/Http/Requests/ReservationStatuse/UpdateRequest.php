<?php

namespace App\Http\Requests\ReservationStatuse;

use App\DTOs\ReservationStatuse\UpdateReservationStatuseDTO;
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
            'name' => [
                'required',
                'string',
                'max:50',
                Rule::unique('reservation_statuses', 'name')->ignore($id)
            ],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'Название',
        ];
    }

    public function toDto(): UpdateReservationStatuseDTO
    {
        return UpdateReservationStatuseDTO::from($this->validated());
    }
}
