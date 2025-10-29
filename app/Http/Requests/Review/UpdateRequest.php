<?php

namespace App\Http\Requests\Review;

use App\DTOs\Review\UpdateReviewDTO;
use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends FormRequest
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
            'description' => ['sometimes', 'string', 'max:1000'],
            'rating' => ['sometimes', 'integer', 'min:1', 'max:5'],
        ];
    }

    public function attributes(): array
    {
        return [
            'description' => 'Описание',
            'rating' => 'Рейтинг',
        ];
    }

    public function toDto(): UpdateReviewDTO
    {
        return UpdateReviewDTO::from($this->validated());
    }
}
