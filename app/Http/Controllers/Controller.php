<?php

namespace App\Http\Controllers;

use App\Models\School;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

abstract class Controller
{
    /**
     * Resolusi sekolah aktif tunggal dan daftar sekolah untuk mode 1 sekolah = 1 database.
     *
     * @return array{0: ?School, 1: int, 2: Collection<int, School>}
     */
    protected function resolveSchoolContext(Request $request): array
    {
        $user = $request->user();

        $school = $user?->school_id
            ? $user->school
            : School::current();

        $schoolId = (int) ($school?->id ?? 0);
        $schools = $school ? collect([$school]) : collect();

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
     * Buat parameter URL redirect bersih tanpa parameter school_id pada mode sekolah tunggal.
     *
     * @param  array<string, mixed>  $params
     * @return array<string, mixed>
     */
    protected function schoolRedirectParams(Request $request, int|School $school, array $params = []): array
    {
        return $params;
    }
}
