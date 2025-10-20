<?php

namespace App\Http\Controllers;

use App\Http\Requests\Chain\IndexRequest;
use App\Http\Resources\ChainCollection;
use App\Services\ChainService;


/**
 * @OA\Tag(
 * name="Chains",
 * description="API для получение информации о сети ресторанов"
 * )
 */
class ChainController extends Controller
{
    public function __construct(
        protected ChainService $chainService
    ) {}


    /**
     * @OA\Get(
     * path="/chains",
     * tags={"Chains"},
     * summary="Получение списка сетей ресторанов",
     * description="Получение списка сетей ресторанов",
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(
     * name="name",
     * in="query",
     * description="Поиск по названию",
     * required=false,
     * @OA\Schema(type="string")
     * ),
     * @OA\Parameter(
     * name="status",
     * in="query",
     * description="Поиск по cтатусу сети ресторана",
     * required=false,
     * @OA\Schema(
     * type="string",
     * enum={"active", "moderation", "rejected", "closed"},
     * default="active"
     * )
     * ),
     * @OA\Parameter(
     * name="page",
     * in="query",
     * description="Номер страницы",
     * required=false,
     * @OA\Schema(type="integer", default=1)
     * ),
     * @OA\Parameter(
     * name="per_page",
     * in="query",
     * description="Количество элементов на странице",
     * required=false,
     * @OA\Schema(type="integer", default=10)
     * ),
     * @OA\Response(
     * response=200,
     * description="Успешное получение списка сетей ресторанов",
     * @OA\JsonContent(
     * @OA\Property(
     * property="data",
     * type="array",
     * description="Массив сетей ресторанов",
     * @OA\Items(
     * @OA\Property(property="id", type="integer", example=1),
     * @OA\Property(property="name", type="string", example="Тестовая сеть"),
     * @OA\Property(property="created_at", type="string", format="date-time", example="13.10.2025 16:58:09"),
     * @OA\Property(property="updated_at", type="string", format="date-time", example="13.10.2025 16:58:09"),
     * ),
     * ),
     * @OA\Property(
     * property="meta",
     * type="object",
     * description="Пагинация",
     * @OA\Property(property="total", type="integer", example=56),
     * @OA\Property(property="per_page", type="integer", example=10),
     * @OA\Property(property="current_page", type="integer", example=1),
     * @OA\Property(property="count_pages", type="integer", example=6),
     * ),
     * )
     * )
     * )
     */
    public function index(IndexRequest $request): ChainCollection
    {
        $filterDto = $request->toDto();

        $chains = $this->chainService->getChains($filterDto, $request->user());

        return new ChainCollection($chains);
    }
}
