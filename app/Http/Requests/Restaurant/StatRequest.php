<?php

namespace App\Http\Requests\Restaurant;

use App\DTOs\Restaurant\RestaurantStatsDTO;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StatRequest extends FormRequest
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
            'period' => ['required', Rule::in(['day', 'month', 'year'])],
            'date' => ['required', 'date_format:Y-m-d'],
        ];
    }

    public function toDto(): RestaurantStatsDTO
    {
        return RestaurantStatsDTO::from($this->validated());
    }
}
