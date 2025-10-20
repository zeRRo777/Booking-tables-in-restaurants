<?php

namespace App\Http\Requests\Chain;

use App\DTOs\Chain\CreateChainDTO;
use App\Models\ChainStatuse;
use App\Models\RestaurantChain;
use Illuminate\Foundation\Http\FormRequest;

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
            'name' => ['required', 'string', 'max:50', 'unique:restaurant_chains,name'],
            'status' => ['required', 'string', 'max:50', 'exists:chain_statuses,name']
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'Название',
            'status' => 'Статус'
        ];
    }

    public function toDto(): CreateChainDTO
    {
        return CreateChainDTO::from($this->validated());
    }
}
