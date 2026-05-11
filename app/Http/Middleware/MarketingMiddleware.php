<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class MarketingMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!\Illuminate\Support\Facades\Auth::check()) {
            return redirect()->route('login');
        }

        if (\Illuminate\Support\Facades\Auth::user()->role !== 'marketing') {
            abort(403, 'Akses ditolak. Hanya role Marketing yang bisa mengakses area ini.');
        }

        return $next($request);
    }
}
