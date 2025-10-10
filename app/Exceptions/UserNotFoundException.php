<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserNotFoundException extends Exception
{
    public function __construct($message = "", $code = 400)
    {
        parent::__construct($message, $code);
    }

    public function render(Request $request): JsonResponse
    {
        return response()->json([
            'type' => config('app.url') . '/errors/user-not-found',
            'title' => 'User not Found',
            'status' => $this->code,
            'detail' =>  $this->message ?? 'Not found user',
            'instance' => $request->getUri(),
        ], $this->code);
    }
}
