<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class RestaurantScheduleCollection extends ResourceCollection
{
    public $collects = RestaurantScheduleResource::class;
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'data' => $this->collection,
        ];
    }

    /**
     * Customize the pagination information for the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  array  $paginated
     * @param  array  $default
     * @return array
     */
    public function paginationInformation($request, $paginated, $default): array
    {
        unset($default['links']);
        $default['meta'] = [
            'total' => $paginated['total'],
            'per_page' => $paginated['per_page'],
            'current_page' => $paginated['current_page'],
            'count_pages' => $paginated['last_page'],
        ];

        return $default;
    }
}
