<?php

namespace App\Http\Middleware;

use App\Models\ApiKey;
use App\Models\Role;
use App\Services\IaeCentralService;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SsoOrApiKeyMiddleware
{
    protected IaeCentralService $iaeService;

    public function __construct(IaeCentralService $iaeService)
    {
        $this->iaeService = $iaeService;
    }

    public function handle(Request $request, Closure $next, string $ability = 'admin'): Response
    {
        $nim = '102022400278';
        $headerKey = $request->header('X-IAE-KEY');

        if ($headerKey !== $nim) {
            return response()->json([
                'status' => 'error',
                'message' => 'API key tidak valid atau tidak memiliki akses.',
                'errors' => null
            ], 401);
        }

        $request->attributes->set('auth_type', 'api_key');
        
        return $next($request);
    }
}
