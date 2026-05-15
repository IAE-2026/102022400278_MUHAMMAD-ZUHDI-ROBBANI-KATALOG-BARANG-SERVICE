<?php

namespace App\Http\Middleware;

use App\Models\ApiKey;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiKeyMiddleware
{
    public function handle(Request $request, Closure $next, string $ability = 'admin'): Response
    {
        $plainKey = $request->bearerToken() ?: $request->header('X-API-Key');

        if ($plainKey === null || $plainKey === '') {
            return $this->unauthorized('BUTUH AKSES ADMIN');
        }

        $apiKey = ApiKey::query()
            ->where('key_hash', hash('sha256', $plainKey))
            ->first();

        if ($apiKey === null || ! $apiKey->can($ability)) {
            return $this->unauthorized('API key tidak valid atau tidak memiliki akses admin.');
        }

        $apiKey->forceFill(['last_used_at' => now()])->save();
        $request->attributes->set('api_key', $apiKey);

        return $next($request);
    }

    private function unauthorized(string $message): JsonResponse
    {
        return response()->json([
            'message' => $message,
        ], 401);
    }
}
