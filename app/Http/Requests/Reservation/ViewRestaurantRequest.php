<?php

namespace App\Http\Requests\Reservation;

use App\DTOs\Reservation\ListReservationForRestaurantDTO;
use App\Models\ReservationStatuse;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ViewRestaurantRequest extends FormRequest
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
            'status' => ['sometimes', 'string', 'exists:reservation_statuses,name'],
            'date_from' => ['sometimes', 'date_format:d.m.Y'],
            'date_to' => ['sometimes', 'date_format:d.m.Y', 'after_or_equal:date_from'],
            'sort_by' => ['sometimes', 'string', Rule::in(['starts_at', 'created_at'])],
            'sort_direction' => ['sometimes', 'string', Rule::in(['asc', 'desc'])],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'table_number' => ['sometimes', 'integer', 'min:1'],
            'table_zone' => ['sometimes', 'string', 'max:255'],
        ];
    }

    public function toDto(): ListReservationForRestaurantDTO
    {
        $dataValidated = $this->validated();

        if (isset($dataValidated['status'])) {
            $dataValidated['status'] = ReservationStatuse::where('name', $dataValidated['status'])->first()->id;
        }
        return ListReservationForRestaurantDTO::from($dataValidated);
    }
}
