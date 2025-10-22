<?php

namespace App\Http\Requests\Restaurant;

use App\DTOs\Restaurant\RestaurantFilterDTO;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
            'type_kitchen' => ['sometimes', 'string', 'max:100'],
            'chain' => ['sometimes', 'string', 'max:50', 'exists:restaurant_chains,name'],
            'status' => ['sometimes', 'string', 'max:50', 'exists:restaurant_statuses,name'],
            'address' => ['sometimes', 'string', 'max:100'],
            'sort_by' => ['sometimes', 'string', Rule::in(['name', 'weekdays_opens_at', 'weekdays_closes_at', 'created_at', 'id'])],
            'sort_direction' => ['sometimes', 'string', Rule::in(['asc', 'desc'])],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'Название',
            'type_kitchen' => 'Тип кухни',
            'chain' => 'Сеть ресторанов',
            'status' => 'Статус',
            'sort_by' => 'Поле сортировки',
            'sort_direction' => 'Направление сортировки',
        ];
    }

    public function toDto(): RestaurantFilterDTO
    {
        $validatedData = $this->validated();

        $user = $this->user();

        if (!$user || !$user->hasAnyRole(['superadmin', 'admin_chain', 'admin_restaurant'])) {
            $validatedData['status'] = 'active';
        }

        return RestaurantFilterDTO::from($validatedData);
    }
}
