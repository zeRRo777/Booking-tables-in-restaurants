<?php

namespace App\Http\Requests;

use App\DTOs\User\AddUserRoleDTO;
use App\Rules\UserHaveRole;
use Illuminate\Foundation\Http\FormRequest;

class StoreUserRoleRequest extends FormRequest
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
            'name' => [
                'required',
                'string',
                'max:50',
                'exists:roles,name',
                new UserHaveRole($this->route('id'))
            ],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'Название роли',
        ];
    }

    public function toDto(): AddUserRoleDTO
    {
        return AddUserRoleDTO::from($this->validated());
    }
}
