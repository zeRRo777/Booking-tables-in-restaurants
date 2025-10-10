<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class UserUpdateQueryException extends Exception
{
    public function __construct($message = "", $code = 500)
    {
        parent::__construct($message, $code);
    }

    public function report(): void
    {
        Log::error('Query update user error: ' . $this->getMessage());
    }

    public function render(Request $request): JsonResponse
    {
        return response()->json([
            'type' => config('app.url') . '/errors/query-update-user',
            'title' => 'Not update User',
            'status' => $this->code,
            'detail' =>  $this->message ?? 'Ошибка при обновлении пользователя.',
            'instance' => $request->getUri(),
        ], $this->code);
    }
}
