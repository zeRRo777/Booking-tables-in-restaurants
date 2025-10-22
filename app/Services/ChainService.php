<?php

namespace App\Services;

use App\DTOs\Chain\ChainFilterDTO;
use App\DTOs\Chain\CreateChainDTO;
use App\DTOs\Chain\UpdateChainDTO;
use App\Models\ChainStatuse;
use App\Models\RestaurantChain;
use App\Models\User;
use App\Repositories\Contracts\ChainRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ChainService
{
    public function __construct(
        protected ChainRepositoryInterface $chainRepository,
    ) {}
    public function getChains(ChainFilterDTO $dto, ?User $user): LengthAwarePaginator
    {
        $query = RestaurantChain::with('status')->forUser($user);

        return $this->chainRepository->applyFiltersAndPaginate($query, $dto);
    }

    public function getChain(int $id): RestaurantChain
    {
        $chain = $this->chainRepository->findById($id);

        if (!$chain) {
            throw new NotFoundHttpException('Сеть ресторанов не найдена!');
        }

        return $chain;
    }

    public function createChain(CreateChainDTO $dto): RestaurantChain
    {
        return $this->chainRepository->create($dto);
    }

    public function updateChain(RestaurantChain $chain, UpdateChainDTO $dto): RestaurantChain
    {
        $data = array_filter(
            $dto->toArray(),
            fn($value) => !is_null($value)
        );

        if (empty($data)) {
            return $chain;
        }

        if (isset($data['status'])) {
            $status = ChainStatuse::where('name', $data['status'])->firstOrFail();
            $data['status_id'] = $status->id;
            unset($data['status']);
        }

        $this->chainRepository->update($chain, $data);

        return $chain->refresh()->load('status');
    }

    public function deleteChain(int $id, bool $real = false): void
    {
        $chain = $this->getChain($id);

        DB::transaction(function () use ($chain, $real): bool {
            return $this->chainRepository->delete($chain, $real);
        });
    }
}
