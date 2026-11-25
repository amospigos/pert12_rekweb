<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiTokenMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $validToken = env('API_TOKEN');
        $incoming = $request->bearerToken();

        if (!$incoming || $incoming !== $validToken) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: Token API tidak valid atau tidak ditemukan.'
            ], 401);
        }

        return $next($request);
    }
}