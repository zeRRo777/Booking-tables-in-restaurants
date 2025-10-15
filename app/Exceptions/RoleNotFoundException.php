<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class RoleNotFoundException extends Exception
{
    public function __construct(string $message = "Role not found.", int $code = Response::HTTP_NOT_FOUND)
    {
        parent::__construct($message, $code);
    }

    public function render(): JsonResponse
    {
        return response()->json([
            'type' => config('app.url') . '/errors/role-not-found',
            'title' => 'Role not found',
            'status' => $this->code,
            'detail' => $this->message,
        ], $this->code);
    }
}
