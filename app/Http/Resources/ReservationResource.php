<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReservationResource extends JsonResource
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
            'starts_at' => $this->starts_at->format('d.m.Y H:i'),
            'ends_at' => $this->ends_at->format('d.m.Y H:i'),
            'special_wish' => $this->special_wish,
            'count_people' => $this->count_people,
            'user' => $this->whenLoaded(
                'user',
                fn() => new UserResource($this->user)
            ),
            'restaurant' => $this->whenLoaded(
                'restaurant',
                function () {
                    return [
                        'id' => $this->restaurant->id,
                        'name' => $this->restaurant->name,
                        'address' => $this->restaurant->address,
                    ];
                }
            ),
            'table' => $this->whenLoaded(
                'table',
                fn() => new TableResource($this->table)
            ),
            'reminder_type' => $this->whenLoaded(
                'reminderType',
                fn() => new ReminderTypeResource($this->reminderType)
            ),
            'status' => $this->whenLoaded('status', fn() => $this->status->name),
        ];
    }
}
