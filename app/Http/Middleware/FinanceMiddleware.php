<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class FinanceMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!\Illuminate\Support\Facades\Auth::check()) {
            return redirect()->route('login');
        }

        if (!in_array(\Illuminate\Support\Facades\Auth::user()->role, ['finance', 'akademik'])) {
            abort(403, 'Unauthorized access. Finance atau Akademik role required.');
        }

        return $next($request);
    }
}
