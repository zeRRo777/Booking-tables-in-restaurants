<?php

namespace App\Http\Requests\Table;

use App\DTOs\Table\CreateTableDTO;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = $this->user();

        if ($user->hasRole('superadmin')) {
            return true;
        }

        $restaurantId = $this->input('restaurant_id');

        if ($restaurantId && $user->hasRole('admin_chain')) {
            return $user->administeredRestaurants()->where('restaurant_id', $this->restaurant_id)->exists();
        }

        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'number' => [
                'required',
                'integer',
                'min:1',
                'max:10000',
                Rule::unique('tables')->where(function ($query) {
                    return $query->where('restaurant_id', $this->restaurant_id);
                }),
            ],
            'capacity_min' => ['required', 'integer', 'min:1', 'lte:capacity_max',],
            'capacity_max' => ['required', 'integer', 'gte:capacity_min'],
            'zone' => ['nullable', 'string', 'max:255'],
            'restaurant_id' => ['required', 'integer', 'exists:restaurants,id'],
        ];
    }

    public function attributes(): array
    {
        return [
            'number' => 'Номер стола',
            'capacity_min' => 'Минимальная вместимость',
            'capacity_max' => 'Максимальная вместимость',
            'zone' => 'Зона',
            'restaurant_id' => 'Ресторан',
        ];
    }

    public function toDto(): CreateTableDTO
    {
        return CreateTableDTO::from($this->validated());
    }
}
