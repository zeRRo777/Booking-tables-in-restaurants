<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class UserNotHaveRoleException extends Exception
{
    public function __construct(string $message = "User dosen't have role", int $code = 400)
    {
        parent::__construct($message, $code);
    }

    public function render(): JsonResponse
    {
        return response()->json([
            'type' => config('app.url') . '/errors/user-not-have-role',
            'title' => "User dosen't have role",
            'status' => $this->code,
            'detail' => $this->message,
        ], $this->code);
    }
}
