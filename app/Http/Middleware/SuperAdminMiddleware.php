<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Support\SidebarCache;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SuperAdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        /** @var User|null $user */
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        if (! $user->is_active) {
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors([
                'login' => 'Akun Anda telah dinonaktifkan.',
            ]);
        }

        // 1. Super Administrator mendapatkan akses penuh ke seluruh rute (root bypass)
        if ($user->isSuperAdmin()) {
            return $next($request);
        }

        // 2. Izinkan rute utilitas umum aplikasi yang wajib bisa diakses oleh seluruh pengguna aktif
        if ($this->isExemptRoute($request)) {
            return $next($request);
        }

        // 3. Cocokkan rute saat ini dengan Menu atau SubMenu terdaftar
        $resolved = SidebarCache::findByRoute($request);

        if (! $resolved) {
            return $this->denyAccess($request, 'Akses ditolak. Halaman atau menu ini tidak terdaftar atau belum aktif.');
        }

        // 4. Tentukan jenis aksi CRUD berdasarkan HTTP method & nama rute
        $action = $this->determineAction($request);

        // 5. Validasi izin granular pengguna terhadap menu atau sub-menu terkait
        $hasAccess = match ($resolved['type']) {
            'submenu' => $user->hasSubMenuAccess($resolved['item'], $action),
            'menu' => $user->hasMenuAccess($resolved['item'], $action),
            default => false,
        };

        if (! $hasAccess) {
            $actionLabel = match ($action) {
                'create' => 'menambah data',
                'edit' => 'mengubah data',
                'delete' => 'menghapus data',
                default => 'melihat halaman ini',
            };

            return $this->denyAccess($request, "Akses ditolak. Anda tidak memiliki izin untuk {$actionLabel}.");
        }

        return $next($request);
    }

    /**
     * Periksa apakah rute request termasuk rute utilitas yang bebas diakses seluruh user terautentikasi.
     */
    protected function isExemptRoute(Request $request): bool
    {
        return $request->routeIs([
            'logout',
            'system.under-development',
            'wilayah.dropdown.*',
            'storage.*',
            '_boost.*',
            'up',
        ]);
    }

    /**
     * Tentukan aksi permission (view, create, edit, delete) dari HTTP request.
     */
    protected function determineAction(Request $request): string
    {
        $routeName = $request->route()?->getName() ?? '';

        if (str_ends_with($routeName, '.create')) {
            return 'create';
        }

        if (str_ends_with($routeName, '.edit')) {
            return 'edit';
        }

        return match ($request->method()) {
            'POST' => 'create',
            'PUT', 'PATCH' => 'edit',
            'DELETE' => 'delete',
            default => 'view',
        };
    }

    /**
     * Respon penolakan akses yang cerdas (JSON, Redirect Back dengan Flash, atau Fallback Menu Pertama).
     */
    protected function denyAccess(Request $request, string $message): Response
    {
        if ($request->expectsJson() || $request->is('api/*')) {
            abort(403, $message);
        }

        // Deteksi riwayat halaman sebelumnya yang sah di dalam aplikasi
        $hasPrevious = $request->hasSession() && $request->session()->has('_previous.url');
        $referer = $request->headers->get('referer');
        $previousUrl = $hasPrevious ? $request->session()->get('_previous.url') : $referer;
        $currentUrl = $request->fullUrl();
        $loginUrl = route('login');

        if ($previousUrl && $previousUrl !== $currentUrl && ! str_starts_with($previousUrl, $loginUrl)) {
            return redirect()->to($previousUrl)->with('error', $message);
        }

        /** @var User|null $user */
        $user = $request->user();

        if ($user) {
            $firstAllowedUrl = SidebarCache::getFirstAllowedUrl($user);

            // Jika user punya menu yang diizinkan dan URL tujuan bukan URL menu tersebut, alihkan ke sana
            if ($firstAllowedUrl && $currentUrl !== $firstAllowedUrl) {
                return redirect()->to($firstAllowedUrl)->with('error', $message);
            }
        }

        abort(403, $message);
    }
}
