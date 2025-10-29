<?php

namespace App\Http\Requests\Review;

use App\DTOs\Review\CreateReviewDTO;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

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
            'description' => ['required', 'string', 'max:1000'],
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'restaurant_id' => ['required', 'integer', 'exists:restaurants,id'],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
        ];
    }

    public function prepareForValidation(): void
    {
        $this->merge([
            'restaurant_id' => $this->route('id'),
            'user_id' => Auth::id(),
        ]);
    }

    public function attributes(): array
    {
        return [
            'description' => 'Описание',
            'user_id' => 'ID пользователя',
            'restaurant_id' => 'ID ресторана',
            'rating' => 'Рейтинг',
        ];
    }

    public function toDto(): CreateReviewDTO
    {
        return CreateReviewDTO::from($this->validated());
    }
}
