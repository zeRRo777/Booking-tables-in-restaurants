<?php

namespace App\Http\Middleware;

use App\Models\UserToken;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Facades\JWTAuth;

class ValidateTokenInDatabase
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            $token = JWTAuth::getToken();
            if (!$token) {
                // Этого не должно случиться, но для надежности
                return response()->json([
                    'type'   => 'https://example.com/errors/token-not-provided',
                    'title'  => 'Token not Provided',
                    'status' => 401,
                    'detail' => 'Токен не найден в бд',
                    'instance' => $request->getUri(),
                ], 401);
            }

            $tokenExists = UserToken::where('token', $token->get())
                ->where('user_id', Auth::id())
                ->where('expires_at', '>', now())
                ->exists();

            if (!$tokenExists) {
                // Если токена нет в БД - разлогиниваем и возвращаем ошибку
                Auth::logout();
                return response()->json([
                    'type' => 'https://example.com/errors/token-not-found',
                    'title'  => 'Token is invalid or has been revoked',
                    'status' => 401,
                    'detail' => 'Токен недействителен или был отозван',
                    'instance' => $request->getUri(),
                ], 401);
            }
        } catch (\Exception $e) {
            return response()->json([
                'type' => 'https://example.com/errors/token',
                'title'  => 'An error occurred while validating token',
                'status' => 500,
                'detail' => 'При проверке токена произошла ошибка',
                'instance' => $request->getUri(),
            ], 500);
        }

        return $next($request);
    }
}
