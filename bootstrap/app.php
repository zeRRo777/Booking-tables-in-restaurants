<?php

use App\Http\Middleware\ForceJsonResponse;
use App\Http\Middleware\ValidateTokenInDatabase;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Request;
use Illuminate\Auth\Access\AuthorizationException;
use Symfony\Component\Finder\Exception\AccessDeniedException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->prependToGroup('api', ForceJsonResponse::class);
        $middleware->throttleWithRedis();
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (ValidationException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'type'   => 'https://example.com/errors/validation-error',
                    'title'  => 'Validation Error',
                    'status' => 422,
                    'detail' => 'Произошла одна или несколько ошибок проверки.',
                    'instance' => $request->getUri(),
                    'errors' => $e->errors(),
                ], 422);
            }
        });
        $exceptions->render(function (AuthenticationException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'type' => 'https://example.com/errors/unauthorized',
                    'title' => 'You not authorized',
                    'status' => 401,
                    'detail' => 'Доступ к ресурсу доступен только авторизованным пользователям!',
                    'instance' => $request->getUri(),
                ], 401);
            }
        });
        $exceptions->render(function (AccessDeniedException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'type'     => 'https://example.com/errors/forbidden',
                    'title'    => 'You not authorized',
                    'status'   => 403,
                    'detail'   => 'Доступ к ресурсу запрещен!',
                    'instance' => $request->getUri(),
                ], 403);
            }
        });
    })->create();
