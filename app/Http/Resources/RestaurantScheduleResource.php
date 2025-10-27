<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RestaurantScheduleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'date' => $this->date->format('d.m.Y'),
            'opens_at' => $this->opens_at->format('H:i'),
            'closes_at' => $this->opens_at->format('H:i'),
            'is_closed' => (bool) $this->is_closed,
            'description' => $this->description,
            'restaurant' => $this->whenLoaded('restaurant', function () {
                return [
                    'id' => $this->restaurant->id,
                    'name' => $this->restaurant->name,
                ];
            }),
            'created_at' => $this->created_at->format('d.m.Y H:i:s'),
        ];
    }
}
