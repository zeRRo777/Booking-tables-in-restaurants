<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MissingEmailChangeException extends Exception
{
    public function __construct($message = "", $code = 401)
    {
        parent::__construct($message, $code);
    }

    public function render(Request $request): JsonResponse
    {
        return response()->json([
            'type' => config('app.url') . '/errors/change-email-token',
            'title' => 'Not Found',
            'status' => $this->code,
            'detail' => $this->message ?? 'Change Email token not found',
            'instance' => $request->getUri(),
        ], $this->code);
    }
}
