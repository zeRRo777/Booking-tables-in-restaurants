<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use OpenApi\Annotations as OA;


class TestController extends Controller
{
    /**
     * @OA\Get(
     * path="/health",
     * summary="Check application health",
     * tags={"Health"},
     * @OA\Response(
     * response=200,
     * description="Application is healthy",
     * @OA\JsonContent(
     * @OA\Schema(ref="#/components/schemas/HealthStatus")
     * )
     * ),
     * @OA\Response(
     * response=503,
     * description="Service unavailable",
     * @OA\JsonContent(
     * @OA\Schema(ref="#/components/schemas/HealthStatus")
     * )
     * )
     * )
     */
    public function health()
    {
        $status = [
            'app' => 'ok',
            'database' => $this->checkdatabaseConnection(),
            'redis' => $this->checkRedisConnection()
        ];

        $statusCode = 200;

        foreach ($status as $service => $serviceStatuse) {
            if ($serviceStatuse !== 'ok') {
                $statusCode = 503;
                break;
            }
        }

        return response()->json($status, $statusCode);
    }

    /**
     * @OA\Schema(
     * schema="HealthStatus",
     * title="Health Status",
     * description="The health status of the application and its services.",
     * @OA\Property(
     * property="app",
     * type="string",
     * example="ok"
     * ),
     * @OA\Property(
     * property="database",
     * type="string",
     * example="ok"
     * ),
     * @OA\Property(
     * property="redis",
     * type="string",
     * example="ok"
     * )
     * )
     */
    protected function checkdatabaseConnection(): string
    {
        try {
            DB::connection()->getPdo();

            return 'ok';
        } catch (\Exception $e) {
            return 'failled';
        }
    }

    protected function checkRedisConnection(): string
    {
        try {
            Redis::ping();
            return 'ok';
        } catch (\Exception $e) {
            return 'failed';
        }
    }
}
