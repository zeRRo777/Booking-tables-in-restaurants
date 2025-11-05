<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TableResource extends JsonResource
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
            'number' => $this->number,
            'capacity_min' => $this->capacity_min,
            'capacity_max' => $this->capacity_max,
            'zone' => $this->zone,
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
