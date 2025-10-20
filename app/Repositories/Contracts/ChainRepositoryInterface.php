<?php

namespace App\Repositories\Contracts;

use App\DTOs\Chain\ChainFilterDTO;
use App\Models\RestaurantChain;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ChainRepositoryInterface
{
    public function getAllFiltered(ChainFilterDTO $dto): LengthAwarePaginator;

    public function getForChainAdminFiltered(ChainFilterDTO $dto, User $user): LengthAwarePaginator;

    public function findById(int $id): ?RestaurantChain;
}
