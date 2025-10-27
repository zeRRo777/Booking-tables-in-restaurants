<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReminderTypeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'minutes' => $this->minutes_before,
            'is_default' => (bool)$this->is_default,
            'created_at' => $this->created_at->format('d.m.Y H:i:s'),
        ];
    }
}
