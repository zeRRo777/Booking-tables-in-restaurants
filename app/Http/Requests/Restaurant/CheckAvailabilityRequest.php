<?php

namespace App\Http\Requests\Restaurant;

use App\DTOs\Restaurant\AvailabilityRestaurantDTO;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CheckAvailabilityRequest extends FormRequest
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
            'date' => ['required', 'date_format:Y-m-d'],
            'time' => ['required', 'date_format:H:i'],
            'zone' => ['nullable', 'string', Rule::exists('tables', 'zone')->where('restaurant_id', $this->input('restaurant_id'))],
            'count_guests' => ['nullable', 'integer', 'min:1', 'max:1000'],
            'restaurant_id' => ['required', 'integer', Rule::exists('restaurants', 'id')->where(function (Builder $query) {
                return $query->whereIn('status_id', function (Builder $subQuery) {
                    $subQuery->select('id')->from('restaurant_statuses')->where('name', 'active');
                });
            })],
            'sort_by' => ['sometimes', 'string', Rule::in(['number'])],
            'sort_direction' => ['sometimes', 'string', Rule::in(['asc', 'desc'])],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ];
    }

    public function prepareForValidation(): void
    {
        $this->merge([
            'restaurant_id' => $this->route('id'),
        ]);
    }

    public function toDto(): AvailabilityRestaurantDTO
    {
        return AvailabilityRestaurantDTO::from($this->validated());
    }
}
