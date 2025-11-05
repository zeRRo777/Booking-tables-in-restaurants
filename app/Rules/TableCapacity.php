<?php

namespace App\Rules;

use App\Models\Table;
use Closure;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;

class TableCapacity implements ValidationRule, DataAwareRule
{
    protected array $data = [];

    public function setData(array $data): static
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $table = Table::find($value);

        $this->data['count_people'] = (int) $this->data['count_people'];

        if ($table && $this->data['count_people'] < $table->capacity_min || $this->data['count_people'] > $table->capacity_max) {
            $fail("Количество гостей ({$this->data['count_people']}) не соответствует вместимости стола (от {$table->capacity_min} до {$table->capacity_max}).");
        }
    }
}
