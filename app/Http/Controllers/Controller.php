<?php

namespace App\Http\Controllers;

use App\Models\School;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

abstract class Controller
{
    /**
     * Resolusi sekolah aktif dan daftar sekolah untuk kebutuhan selector halaman multi-tenancy.
     *
     * @return array{0: ?School, 1: int, 2: Collection<int, School>}
     */
    protected function resolveSchoolContext(Request $request): array
    {
        $user = $request->user();

        // 1. Dropdown daftar sekolah hanya ditarik jika Super Admin
        $schools = collect();
        if ($user?->isSuperAdmin()) {
            $schools = School::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name', 'npsn', 'school_type']);
        }

        // 2. Tentukan sekolah terpilih
        $school = null;
        $schoolId = 0;

        if ($user?->school_id) {
            $school = $user->school;
            $schoolId = (int) $user->school_id;
        } elseif ($user?->isSuperAdmin()) {
            $defaultSchool = $schools->first();
            $selectedId = $request->integer('school_id', $defaultSchool?->id ?? 0);
            $school = $schools->firstWhere('id', $selectedId) ?? $defaultSchool;
            $schoolId = (int) ($school?->id ?? 0);
        }

        return [$school, $schoolId, $schools];
    }

    /**
     * Pastikan pengguna non-superadmin tidak mengakses atau memanipulasi data milik unit sekolah lain.
     */
    protected function authorizeSchool(Request $request, int|School $school): void
    {
        $user = $request->user();
        $targetSchoolId = $school instanceof School ? $school->id : $school;

        if ($user?->school_id && (int) $user->school_id !== (int) $targetSchoolId) {
            abort(403, 'Akses ditolak. Anda tidak memiliki izin untuk mengelola data sekolah ini.');
        }
    }

    /**
     * Buat parameter URL redirect dengan menyertakan school_id hanya jika pengguna adalah Super Admin.
     *
     * @param  array<string, mixed>  $params
     * @return array<string, mixed>
     */
    protected function schoolRedirectParams(Request $request, int|School $school, array $params = []): array
    {
        if ($request->user()?->isSuperAdmin()) {
            return array_merge([
                'school_id' => $school instanceof School ? $school->id : $school,
            ], $params);
        }

        return $params;
    }
}
