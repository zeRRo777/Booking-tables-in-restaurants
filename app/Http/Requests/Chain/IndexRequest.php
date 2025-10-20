<?php

namespace App\Http\Requests\Chain;

use App\DTOs\Chain\ChainFilterDTO;
use Illuminate\Foundation\Http\FormRequest;

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
        $user = $this->user();

        $rules = [
            'name' => ['sometimes', 'string', 'max:50'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ];

        if ($user && ($user->hasRole('superadmin') || $user->hasRole('admin_chain'))) {
            $rules['status'] = ['sometimes', 'exists:chain_statuses,name'];
        }

        return $rules;
    }

    public function attributes(): array
    {
        return [
            'name' => 'Название сети',
            'per_page' => 'Количество пользователей на странице',
            'status' => 'Статус сети',
        ];
    }

    public function toDto(): ChainFilterDTO
    {
        $validatedData = $this->validated();

        $user = $this->user();

        if (!$user || !($user->hasRole('superadmin') || $user->hasRole('admin_chain'))) {
            $validatedData['status'] = 'active';
        }

        return ChainFilterDTO::from($validatedData);
    }
}
