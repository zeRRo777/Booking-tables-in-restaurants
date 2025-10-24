<?php

namespace App\Http\Requests\Table;

use App\DTOs\Table\UpdateTableDTO;
use App\Models\Table;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRequest extends FormRequest
{
    protected $tableInstance;
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = $this->user();

        if ($user->hasRole('superadmin')) {
            return true;
        }

        if ($user->hasRole('admin_restaurant')) {
            return $user->administeredRestaurants()->where('restaurant_id', $this->getTableInstance()->restaurant_id)->exists();
        }

        return false;
    }

    protected function getTableInstance(): Table
    {
        if ($this->tableInstance) {
            return $this->tableInstance;
        }

        $idTable = $this->route('id');

        return $this->tableInstance = Table::find($idTable);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $table = $this->getTableInstance();

        $capacityMin = $this->input('capacity_min', $table->capacity_min);
        $capacityMax = $this->input('capacity_max', $table->capacity_max);

        return [
            'number' => [
                'sometimes',
                'integer',
                'min:1',
                'max:10000',
                Rule::unique('tables')->where(function ($query) use ($table) {
                    return $query->where('restaurant_id', $table->restaurant_id);
                })->ignore($this->route('id')),
            ],
            'capacity_min' => ['sometimes', 'integer', 'min:1', "lte:{$capacityMax}"],
            'capacity_max' => ['sometimes', 'integer', "gte:{$capacityMin}"],
            'zone' => ['sometimes', 'nullable', 'string', 'max:255'],
        ];
    }

    public function toDto(): UpdateTableDTO
    {
        return UpdateTableDTO::from($this->validated());
    }
}
