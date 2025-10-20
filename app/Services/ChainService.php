<?php

namespace App\Services;

use App\DTOs\Chain\ChainFilterDTO;
use App\DTOs\Chain\CreateChainDTO;
use App\Exceptions\ChainNotFoundException;
use App\Http\Resources\ChainResourse;
use App\Models\RestaurantChain;
use App\Models\User;
use App\Repositories\Contracts\ChainRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ChainService
{
    public function __construct(
        protected ChainRepositoryInterface $chainRepository,
    ) {}
    public function getChains(ChainFilterDTO $dto, ?User $user): LengthAwarePaginator
    {
        if ($user && $user->hasRole('superadmin')) {
            return $this->chainRepository->getAllFiltered($dto);
        }

        if ($user && $user->hasRole('admin_chain')) {
            return $this->chainRepository->getForChainAdminFiltered($dto, $user);
        }

        $dto->status = 'active';

        return $this->chainRepository->getAllFiltered($dto);
    }

    public function getChain(int $id): RestaurantChain
    {
        $chain = $this->chainRepository->findById($id);

        if (!$chain) {
            throw new ChainNotFoundException('Сеть ресторанов не найдена!');
        }

        return $chain;
    }

    public function createChain(CreateChainDTO $dto): RestaurantChain
    {
        return $this->chainRepository->create($dto);
    }
}
