<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class ChainNotFoundException extends Exception
{
    public function __construct(string $message = "Chain not found.", int $code = Response::HTTP_NOT_FOUND)
    {
        parent::__construct($message, $code);
    }

    public function render(): JsonResponse
    {
        return response()->json([
            'type' => config('app.url') . '/errors/chain-not-found',
            'title' => 'Chain not found',
            'status' => $this->code,
            'detail' => $this->message,
        ], $this->code);
    }
}
