<?php

namespace App\Http\Controllers;

use OpenApi\Annotations as OA;

/**
 * @OA\Info(
 * version="1.0.0",
 * title="Booking tables in restaurants API",
 * description="API documentation for the restaurant booking system.",
 * @OA\Contact(
 * email="your-email@example.com"
 * ),
 * )
 *
 * @OA\Server(
 * url=L5_SWAGGER_CONST_HOST,
 * description="API Server"
 * )
 *
 * @OA\SecurityScheme(
 * securityScheme="bearerAuth",
 * in="header",
 * name="bearerAuth",
 * type="http",
 * scheme="bearer",
 * bearerFormat="JWT",
 * )
 */
abstract class Controller
{
    //
}
