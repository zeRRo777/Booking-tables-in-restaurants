<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthenticateException extends Exception
{
    public function __construct($message = "", $code = 401)
    {
        parent::__construct($message, $code);
    }

    public function render(Request $request): JsonResponse
    {
        return response()->json([
            'type' => config('app.url') . '/errors/unauthorized',
            'title' => 'Unauthorized',
            'status' => $this->code,
            'detail' =>  $this->message ?? 'Неверные учетные данные.',
            'instance' => $request->getUri(),
        ], $this->code);
    }
}
