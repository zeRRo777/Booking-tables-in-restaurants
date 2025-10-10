<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TokenExpiredException extends Exception
{
    public function __construct($message = "", $code = 401)
    {
        parent::__construct($message, $code);
    }

    public function render(Request $request): JsonResponse
    {
        return response()->json([
            'type' => config('app.url') . '/errors/token-expired',
            'title' => 'Token expired',
            'status' => $this->code,
            'detail' => $this->message ?? 'Token expired',
            'instance' => $request->getUri(),
        ], $this->code);
    }
}
