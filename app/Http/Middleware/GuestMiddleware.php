<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class GuestMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()) {
            return response()->json([
                'type'   => 'https://example.com/errors/already-authenticated',
                'title'  => 'Already Authenticated',
                'status' => 403,
                'detail' => 'Вы уже авторизованы!',
                'instance' => $request->getUri(),
            ], 403);
        }
        return $next($request);
    }
}
