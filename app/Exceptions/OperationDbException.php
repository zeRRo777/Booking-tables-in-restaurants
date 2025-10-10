<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class OperationDbException extends Exception
{
    public function __construct($message = "", $code = 500)
    {
        parent::__construct($message, $code);
    }

    public function report(): void
    {
        Log::error('Operation DB error: ' . $this->getMessage());
    }

    public function render(Request $request): JsonResponse
    {
        return response()->json([
            'type' => config('app.url') . '/errors/operation-db',
            'title' => 'Operation DB error',
            'status' => $this->code,
            'detail' => $this->message ?? 'Operation DB error',
            'instance' => $request->getUri(),
        ], $this->code);
    }
}
