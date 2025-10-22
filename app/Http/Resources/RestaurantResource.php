<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RestaurantResource extends JsonResource
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
            'description' => $this->description,
            'address' => $this->address,
            'type_kitchen' => $this->type_kitchen,
            'price_range' => $this->price_range,
            'working_hours' => [
                'weekdays' => [
                    'opens_at' => $this->weekdays_opens_at?->format('H:i'),
                    'closes_at' => $this->weekdays_closes_at?->format('H:i'),
                ],
                'weekend' => [
                    'opens_at' => $this->weekend_opens_at?->format('H:i'),
                    'closes_at' => $this->weekend_closes_at?->format('H:i'),
                ]
            ],
            'chain' => $this->whenLoaded('chain', fn() => new ChainResourse($this->chain)),
            'status' => $this->whenLoaded('status', fn() => $this->status?->name),
            'cancellation_policy' => $this->cancellation_policy,
            'created_at' => $this->created_at->format('d.m.Y H:i:s'),
            'updated_at' => $this->updated_at->format('d.m.Y H:i:s'),
        ];
    }
}
