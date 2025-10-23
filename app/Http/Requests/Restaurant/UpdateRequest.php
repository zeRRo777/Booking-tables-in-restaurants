<?php

namespace App\Http\Requests\Restaurant;

use App\DTOs\Restaurant\UpdateRestaurantDTO;
use App\Models\Restaurant;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = $this->user();

        if (!$this->has('restaurant_chain_id')) {
            return true;
        }

        $newChainId = $this->input('restaurant_chain_id');
        $restaurantId = $this->route('id');

        $restaurant = Restaurant::find($restaurantId);

        if ($restaurant->restaurant_chain_id == $newChainId) {
            return true;
        }

        if ($user->hasRole('admin_chain')) {
            if ($newChainId === null) {
                return true;
            }

            return $user->administeredChains()->where('id', $newChainId)->exists();
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
        $idRestaurant = $this->route('id');
        return [
            'name' => ['required', 'string', 'max:100', Rule::unique('restaurants', 'name')->ignore($idRestaurant)],
            'description' => ['nullable', 'string', 'max:5000'],
            'address' => ['sometimes', 'string', 'max:100', Rule::unique('restaurants', 'address')->ignore($idRestaurant)],
            'type_kitchen' => ['nullable', 'string', 'max:100'],
            'price_range' => ['nullable', 'string', 'max:20'],
            'weekdays_opens_at' => ['nullable', 'date_format:H:i'],
            'weekdays_closes_at' => ['nullable', 'date_format:H:i', 'after:weekdays_opens_at'],
            'weekend_opens_at' => ['nullable', 'date_format:H:i'],
            'weekend_closes_at' => ['nullable', 'date_format:H:i', 'after:weekend_opens_at'],
            'cancellation_policy' => ['nullable', 'string', 'max:5000'],
            'restaurant_chain_id' => ['nullable', 'integer', 'exists:restaurant_chains,id'],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'Название ресторана',
            'description' => 'Описание ресторана',
            'address' => 'Адрес ресторана',
            'type_kitchen' => 'Тип кухни',
            'price_range' => 'Ценовой диапазон',
            'weekdays_opens_at' => 'Время открытия в будни',
            'weekdays_closes_at' => 'Время закрытия в будни',
            'weekend_opens_at' => 'Время открытия в выходные',
            'weekend_closes_at' => 'Время закрытия в выходные',
            'cancellation_policy' => 'Политика отмены',
            'restaurant_chain_id' => 'Сеть ресторана',
        ];
    }

    public function toDto(): UpdateRestaurantDTO
    {
        return UpdateRestaurantDTO::from($this->validated());
    }
}
