<?php

namespace App\Http\Requests\Restaurant\Blocked;

use App\DTOs\Restaurant\CreateUserRestaurantBlockedDTO;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

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
            'user_id' => [
                'required',
                'exists:users,id',
                Rule::unique('user_restaurant_blocked')->where(function ($query) {
                    return $query->where('restaurant_id', $this->input('restaurant_id'));
                }),
            ],
            'restaurant_id' => ['required', 'exists:restaurants,id'],
            'block_reason' => ['nullable', 'string', 'max:1000'],
            'blocked_by' => ['required', 'exists:users,id'],
        ];
    }

    public function attributes(): array
    {
        return [
            'user_id' => 'Пользователь',
            'restaurant_id' => 'Ресторан',
            'block_reason' => 'Причина блокировки',
        ];
    }

    public function prepareForValidation(): void
    {
        $this->merge(
            [
                'restaurant_id' => $this->route('id'),
                'blocked_by' => Auth::id(),
            ],
        );
    }

    public function toDto(): CreateUserRestaurantBlockedDTO
    {
        return CreateUserRestaurantBlockedDTO::from($this->validated());
    }
}
