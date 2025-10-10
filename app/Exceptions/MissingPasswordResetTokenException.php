<?php

namespace App\Exceptions;

use Exception;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MissingPasswordResetTokenException extends Exception
{
    public function __construct($message = "", $code = 401)
    {
        parent::__construct($message, $code);
    }

    public function render(Request $request): JsonResponse
    {
        return response()->json([
            'type' => config('app.url') . '/errors/reset-password-token',
            'title' => 'Not Found',
            'status' => $this->code,
            'detail' => $this->message ?? 'Reset password token not found',
            'instance' => $request->getUri(),
        ], $this->code);
    }
}
