<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyApiKey
{
    public function handle(Request $request, Closure $next): Response
    {
        $configuredKey = (string) config('services.api.key');
        $providedKey = (string) $request->header('X-API-KEY');

        if ($configuredKey === '' || $providedKey === '' || !hash_equals($configuredKey, $providedKey)) {
            return response()->json([
                'message' => 'API key tidak valid.',
            ], 401);
        }

        return $next($request);
    }
}
