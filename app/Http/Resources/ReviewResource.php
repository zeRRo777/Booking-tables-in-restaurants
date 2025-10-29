<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReviewResource extends JsonResource
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
            'description' => $this->description,
            'user' => $this->whenLoaded('user', function () {
                return [
                    'id' => $this->user->id,
                    'name' => $this->user->name,
                ];
            }),
            'rating' => $this->rating,
            'restaurant' => $this->whenLoaded('restaurant', function () {
                return [
                    'id' => $this->restaurant->id,
                    'name' => $this->restaurant->name,
                    'address' => $this->restaurant->address,
                    'type_kitchen' => $this->restaurant->type_kitchen,
                    'chain' => $this->restaurant->chain ? [
                        'id' => $this->restaurant->chain->id,
                        'name' => $this->restaurant->chain->name,
                    ] : null,
                ];
            }),
            'created_at' => $this->created_at,
        ];
    }
}
