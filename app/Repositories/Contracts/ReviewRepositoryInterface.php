<?php

namespace App\Repositories\Contracts;

use App\DTOs\Review\ReviewFilterDTO;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ReviewRepositoryInterface
{
    public function getFiltered(ReviewFilterDTO $dto): LengthAwarePaginator;
}
