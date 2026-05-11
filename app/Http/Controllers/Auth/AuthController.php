<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Auth\Events\PasswordReset;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    /**
     * Show login form.
     */
    public function showLoginForm()
    {
        if (Auth::check()) {
            return $this->redirectToDashboard();
        }
        
        return view('auth.login');
    }

    /**
     * Handle login request.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $credentials['email'] = Str::lower(trim($credentials['email']));
        $throttleKey = $this->throttleKey($request);
        $maxAttempts = $this->loginMaxAttempts();
        $decaySeconds = $this->loginDecaySeconds();

        if (RateLimiter::tooManyAttempts($throttleKey, $maxAttempts)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            return back()->withErrors([
                'email' => 'Terlalu banyak percobaan login. Coba lagi dalam ' . $seconds . ' detik.',
            ])->withInput($request->only('email'));
        }

        $remember = $request->has('remember');

        if (Auth::attempt($credentials, $remember)) {
            RateLimiter::clear($throttleKey);
            $request->session()->regenerate();

            // Log activity
            \App\Models\ActivityLog::createLog(
                'login',
                'User',
                \Illuminate\Support\Facades\Auth::id(),
                \Illuminate\Support\Facades\Auth::user()->name . ' berhasil login'
            );

            return $this->redirectToDashboard();
        }

        RateLimiter::hit($throttleKey, $decaySeconds);

        return back()->withErrors([
            'email' => 'Email atau password salah.',
        ])->withInput($request->only('email'));
    }

    /**
     * Build a unique throttle key for login attempts.
     */
    protected function throttleKey(Request $request): string
    {
        return Str::lower((string) $request->input('email')) . '|' . $request->ip();
    }

    /**
     * Maximum login attempts before temporary lock.
     */
    protected function loginMaxAttempts(): int
    {
        return (int) env('LOGIN_MAX_ATTEMPTS', 8);
    }

    /**
     * Lock duration in seconds after failed attempts.
     */
    protected function loginDecaySeconds(): int
    {
        return (int) env('LOGIN_DECAY_SECONDS', 45);
    }

    /**
     * Show register form.
     */
    public function showRegisterForm()
    {
        if (Auth::check()) {
            return $this->redirectToDashboard();
        }
        
        return view('auth.register');
    }

    /**
     * Handle register request.
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'required|string|max:20',
            'company_name' => 'nullable|string|max:255',
            'company_address' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Create user with client role by default
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'client',
            'phone' => $request->phone,
        ]);

        // Create client profile if company info provided
        if ($request->filled('company_name')) {
            \App\Models\Client::create([
                'user_id' => $user->id,
                'company_name' => $request->company_name,
                'company_address' => $request->company_address,
                'contact_person' => $request->name,
                'contact_phone' => $request->phone,
            ]);
        }

        // Log activity
        \App\Models\ActivityLog::createLog(
            'register',
            'User',
            $user->id,
            $user->name . ' mendaftar sebagai client baru'
        );

        // Auto login after register
        Auth::login($user);

        return redirect()->route('client.dashboard')
            ->with('success', 'Registrasi berhasil! Selamat datang di sistem kami.');
    }

    /**
     * Handle logout request.
     */
    public function logout(Request $request)
    {
        // Log activity before logout
        \App\Models\ActivityLog::createLog(
            'logout',
            'User',
            \Illuminate\Support\Facades\Auth::id(),
            \Illuminate\Support\Facades\Auth::user()->name . ' logout'
        );

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Hapus remember_me cookie untuk mencegah auto-login
        $cookie = \Illuminate\Support\Facades\Cookie::forget('remember_web');

        return redirect()->route('login', ['v' => time()])
            ->with('success', 'Anda telah logout.')
            ->withCookie($cookie);
    }

    /**
     * Redirect to appropriate dashboard based on role.
     */
    protected function redirectToDashboard()
    {
        $user = Auth::user();

        // Redirect based on role
        switch ($user->role) {
            case 'admin':  // Super Admin, Admin Agency, Admin Academy
            case 'superadmin':
                return redirect()->route('admin.dashboard');
            
            case 'marketing':
                return redirect()->route('admin.dashboard');

            case 'akademik':
                return redirect()->route('akademik.dashboard');
            
            case 'finance':
                return redirect()->route('finance.dashboard');
            
            case 'trainer':
                return redirect()->route('trainer.dashboard');
            
            case 'client':
                return redirect()->route('client.dashboard');
            
            case 'employee':
                return redirect()->route('employee.dashboard');
            
            default:
                // Fallback: logout user dengan role tidak valid
                Auth::logout();
                return redirect()->route('login')->withErrors(['error' => 'Role tidak valid']);
        }
    }

    /**
     * Show forgot password form.
     */
    public function showForgotPasswordForm()
    {
        return view('auth.forgot-password');
    }

    /**
     * Send reset link to email.
     */
    public function sendResetLink(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        // Check if user exists
        $user = User::where('email', $request->email)->first();
        
        if (!$user) {
            return back()->withErrors(['email' => 'Email tidak ditemukan dalam sistem.']);
        }

        // Check if user is admin or employee only
        if (!in_array($user->role, ['admin', 'employee'])) {
            return back()->withErrors(['email' => 'Reset password hanya untuk Admin & Employee.']);
        }

        // Send password reset link
        $status = Password::sendResetLink(
            $request->only('email')
        );

        return $status === Password::RESET_LINK_SENT
            ? back()->with('status', 'Link reset password telah dikirim ke email Anda!')
            : back()->withErrors(['email' => __($status)]);
    }

    /**
     * Show reset password form.
     */
    public function showResetPasswordForm(Request $request, $token)
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->email
        ]);
    }

    /**
     * Reset password.
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->setRememberToken(Str::random(60));

                $user->save();

                event(new PasswordReset($user));

                // Log activity
                \App\Models\ActivityLog::createLog(
                    'password_reset',
                    'User',
                    $user->id,
                    $user->name . ' melakukan reset password'
                );
            }
        );

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('login')->with('success', 'Password berhasil direset! Silakan login dengan password baru.')
            : back()->withErrors(['email' => __($status)]);
    }

    // ...Google OAuth methods dihapus...
}
