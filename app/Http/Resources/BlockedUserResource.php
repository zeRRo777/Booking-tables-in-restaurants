<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BlockedUserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->whenLoaded('user', fn() => $this->user->id),
            'name' => $this->whenLoaded('user', fn() => $this->user->name),
            'email' => $this->whenLoaded('user', fn() => $this->user->email),
            'phone' => $this->whenLoaded('user', fn() => $this->user->phone),
            'block_info' => [
                'block_reason' => $this->block_reason,
                'blocked_at' => $this->updated_at->format('d.m.Y H:i:s'),
                'blocked_by' => $this->whenLoaded('blocker', function () {
                    return new UserResource($this->blocker);
                }),
            ]
        ];
    }
}
