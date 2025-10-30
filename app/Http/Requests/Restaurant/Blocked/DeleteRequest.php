<?php

namespace App\Http\Requests\Restaurant\Blocked;

use App\DTOs\Restaurant\DeleteUserRestaurantBlockedDTO;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DeleteRequest extends FormRequest
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
            'restaurant_id' => ['required', 'integer'],
            'user_id' => [
                'required',
                'integer',
                Rule::exists('user_restaurant_blocked')->where(function ($query) {
                    return $query->where('restaurant_id', $this->input('restaurant_id'));
                }),
            ],
        ];
    }

    public function prepareForValidation(): void
    {
        $this->merge([
            'restaurant_id' => $this->route('restaurant_id'),
            'user_id' => $this->route('user_id'),
        ]);
    }

    public function attributes(): array
    {
        return [
            'restaurant_id' => 'ID ресторана',
            'user_id' => 'ID пользователя',
        ];
    }

    public function toDto(): DeleteUserRestaurantBlockedDTO
    {
        return DeleteUserRestaurantBlockedDTO::from($this->validated());
    }
}
