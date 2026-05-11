<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
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

        $allowedRoles = ['admin', 'superadmin', 'marketing', 'akademik'];

        if (!in_array(\Illuminate\Support\Facades\Auth::user()->role, $allowedRoles)) {
            abort(403, 'Unauthorized. Admin access only.');
        }

        return $next($request);
    }
}
