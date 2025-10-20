<?php

namespace App\Http\Requests\Chain;

use App\DTOs\Chain\UpdateChainDTO;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
            'name' => ['sometimes', 'string', 'max:50', Rule::unique('restaurant_chains')->ignore($this->route('id'))],
            'status' => ['sometimes', 'string', 'max:50', 'exists:chain_statuses,name'],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'Название',
            'status' => 'Статус',
        ];
    }

    public function toDto(): UpdateChainDTO
    {
        $validatedData = $this->validated();

        $user = $this->user();

        if ($user->hasRole('admin_chain')) {
            if (isset($validatedData['status'])) {
                unset($validatedData['status']);
            }
        }

        return UpdateChainDTO::from($validatedData);
    }
}
