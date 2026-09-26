<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\User;
use App\Support\SidebarCache;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class LoginController extends Controller
{
    /**
     * Display the login view.
     */
    public function showLoginForm(): View|RedirectResponse
    {
        if (Auth::check()) {
            return Auth::user()->isAdmin()
                ? redirect()->route('dashboard.admin')
                : redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'login' => ['required', 'string'],
            'password' => ['required', 'string'],
        ], [
            'login.required' => 'Username atau Email wajib diisi.',
            'password.required' => 'Kata sandi wajib diisi.',
        ]);

        $loginInput = trim($credentials['login']);
        $loginField = filter_var($loginInput, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        // 1. Attempt authentication with active account constraint
        if (Auth::attempt([$loginField => $loginInput, 'password' => $credentials['password'], 'is_active' => true], $request->boolean('remember'))) {
            $request->session()->regenerate();

            /** @var User $user */
            $user = Auth::user();

            if (! $user->isSuperAdmin()) {
                $targetDashboardRoute = $user->isAdmin() ? 'dashboard.admin' : 'dashboard';
                $dashboardMenu = Menu::where('route_name', $targetDashboardRoute)->first()
                    ?? Menu::where('route_name', 'dashboard')->first();
                $hasDashboardAccess = $dashboardMenu && $user->hasMenuAccess($dashboardMenu, 'view');

                if (! $hasDashboardAccess) {
                    $firstAllowedUrl = SidebarCache::getFirstAllowedUrl($user);

                    if ($firstAllowedUrl) {
                        return redirect()->intended($firstAllowedUrl);
                    }

                    // Pengguna tidak memiliki izin menu apa pun
                    Auth::logout();
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();

                    throw ValidationException::withMessages([
                        'login' => 'Akun Anda belum memiliki hak akses menu aktif. Silakan hubungi pengelola sistem.',
                    ]);
                }
            }

            if ($user->isAdmin()) {
                return redirect()->intended(route('dashboard.admin'));
            }

            return redirect()->intended(route('dashboard'));
        }

        // 2. If login failed, check if credentials match an inactive account (valid password required)
        if (Auth::validate([$loginField => $loginInput, 'password' => $credentials['password']])) {
            throw ValidationException::withMessages([
                'login' => 'Akun Anda berstatus non-aktif. Silakan hubungi pengelola sistem.',
            ]);
        }

        // 3. Otherwise return standard invalid credentials error
        throw ValidationException::withMessages([
            'login' => 'Kredensial yang diberikan tidak cocok dengan data kami.',
        ]);
    }

    /**
     * Destroy an authenticated session.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
