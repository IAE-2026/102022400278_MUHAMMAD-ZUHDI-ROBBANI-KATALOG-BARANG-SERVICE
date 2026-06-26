<?php

use App\Http\Middleware\ApiKeyMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {

    $middleware->alias([
        'apikey' => \App\Http\Middleware\ApiKeyMiddleware::class,
        'sso' => \App\Http\Middleware\SsoOrApiKeyMiddleware::class,
    ]);

})
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (\Illuminate\Validation\ValidationException $e, $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'status' => 'error',
                    'message' => $e->getMessage(),
                    'errors' => $e->errors()
                ], 422);
            }
        });

        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e, $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Resource not found',
                    'errors' => null
                ], 404);
            }
        });

        $exceptions->render(function (\Throwable $e, $request) {
            if ($request->is('api/*')) {
                $statusCode = $e instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface 
                    ? $e->getStatusCode() 
                    : 500;

                return response()->json([
                    'status' => 'error',
                    'message' => $e->getMessage() ?: 'Internal Server Error',
                    'errors' => null
                ], $statusCode);
            }
        });
    })->create();
