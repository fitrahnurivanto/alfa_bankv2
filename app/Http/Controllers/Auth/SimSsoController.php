<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\SsoToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class SimSsoController extends Controller
{
    public function start(Request $request)
    {
        abort_unless(in_array(Auth::user()->role, ['admin', 'akademik', 'marketing'], true), 403);

        $token = Str::random(64);
        SsoToken::create([
            'user_id' => Auth::id(),
            'token_hash' => hash('sha256', $token),
            'target' => 'sim',
            'expires_at' => now()->addSeconds(60),
        ]);

        return redirect()->away(rtrim((string) config('services.sim.sso_url'), '/') . '?sso_token=' . urlencode($token));
    }
}