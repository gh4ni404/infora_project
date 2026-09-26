<?php

namespace App\Http\Controllers;

use App\Models\School;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the initial Super Admin Dashboard.
     */
    public function index(Request $request): View|RedirectResponse
    {
        if ($request->user()->isAdmin()) {
            return redirect()->route('dashboard.admin');
        }

        return view('dashboard', [
            'user' => $request->user(),
            'systemInfo' => [
                'laravel_version' => app()->version(),
                'php_version' => PHP_VERSION,
                'environment' => config('app.env'),
                'timezone' => config('app.timezone'),
            ],
        ]);
    }

    /**
     * Display the School Administrator Dashboard.
     */
    public function admin(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();

        // 1 & 2. Resolusi konteks sekolah dan daftar pilihan sekolah
        [$school, $schoolId, $schools] = $this->resolveSchoolContext($request);

        // Load relasi wilayah jika ada sekolah
        if ($school) {
            $school->loadMissing(['provinsi', 'kabupaten', 'kecamatan', 'kelurahan']);
        }

        // 3. Ringkasan Metrik
        $totalJurusan = $school ? $school->jurusans()->count() : 0;
        $totalGuru = $school ? $school->users()->where('user_type', 'guru')->count() : 0;
        $totalMurid = $school ? $school->users()->whereIn('user_type', ['murid', 'siswa'])->count() : 0;
        $activeTahunAjaran = $school?->activeTahunAjaran();
        $activeSemester = $school?->activeSemester();

        // 4. Agenda Kalender Akademik Terdekat (hingga 5 agenda mendatang)
        $upcomingEvents = $school
            ? $school->kalenderAkademik()
                ->whereDate('tanggal_selesai', '>=', now()->toDateString())
                ->orderBy('tanggal_mulai', 'asc')
                ->take(5)
                ->get()
            : collect();

        return view('dashboard.admin', compact(
            'user',
            'school',
            'schools',
            'totalJurusan',
            'totalGuru',
            'totalMurid',
            'activeTahunAjaran',
            'activeSemester',
            'upcomingEvents'
        ));
    }
}
