<?php

namespace App\Exceptions;

use Exception;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MissingEmailVerificationCodeException extends Exception
{
    public function __construct($message = "", $code = 401)
    {
        parent::__construct($message, $code);
    }

    public function render(Request $request): JsonResponse
    {
        return response()->json([
            'type' => config('app.url') . '/errors/email-verify-code',
            'title' => 'Not Found',
            'status' => $this->code,
            'detail' => $this->message ?? 'Email verification code not found',
            'instance' => $request->getUri(),
        ], $this->code);
    }
}
