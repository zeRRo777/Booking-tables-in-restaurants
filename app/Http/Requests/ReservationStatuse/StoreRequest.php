<?php

namespace App\Http\Requests\ReservationStatuse;

use App\DTOs\ReservationStatuse\CreateReservationStatuseDTO;
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
            'name' => ['required', 'string', 'max:50', 'unique:reservation_statuses,name'],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'Название',
        ];
    }

    public function toDto(): CreateReservationStatuseDTO
    {
        return CreateReservationStatuseDTO::from($this->validated());
    }
}
