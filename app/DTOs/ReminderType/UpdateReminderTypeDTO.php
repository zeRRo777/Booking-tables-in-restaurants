<?php

namespace App\DTOs\ReminderType;

use Spatie\LaravelData\Data;

class UpdateReminderTypeDTO extends Data
{
    public function __construct(
        public ?string $name,
        public ?int $minutes_before,
        public ?bool $is_default,
    ) {}
}
