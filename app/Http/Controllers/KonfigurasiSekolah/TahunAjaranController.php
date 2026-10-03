<?php

namespace App\Http\Controllers\KonfigurasiSekolah;

use App\Http\Controllers\Controller;
use App\Http\Requests\KonfigurasiSekolah\StoreSemesterRequest;
use App\Http\Requests\KonfigurasiSekolah\StoreTahunAjaranRequest;
use App\Http\Requests\KonfigurasiSekolah\UpdateSemesterRequest;
use App\Http\Requests\KonfigurasiSekolah\UpdateTahunAjaranRequest;
use App\Models\KalenderAkademik;
use App\Models\Semester;
use App\Models\TahunAjaran;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TahunAjaranController extends Controller
{
    /**
     * Menampilkan antarmuka master-detail konfigurasi Tahun Ajaran dan Semester.
     */
    public function index(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();

        // 1 & 2. Tentukan sekolah terpilih (konsisten dengan pendekatan multi-tenancy)
        [$selectedSchool, $selectedSchoolId, $schools] = $this->resolveSchoolContext($request);

        // 3. Ambil daftar Tahun Ajaran untuk unit sekolah terpilih (Master Table)
        $tahunAjaranList = collect();
        if ($selectedSchoolId > 0) {
            $tahunAjaranList = TahunAjaran::query()
                ->where('school_id', $selectedSchoolId)
                ->withCount('semesters')
                ->with('semesters')
                ->orderBy('tahun', 'desc')
                ->get();
        }

        // 4. Tentukan Tahun Ajaran terpilih (Detail Table)
        $selectedTahunAjaranId = (int) $request->query('tahun_ajaran_id', 0);
        $selectedTahunAjaran = null;

        if ($selectedTahunAjaranId > 0) {
            $selectedTahunAjaran = $tahunAjaranList->firstWhere('id', $selectedTahunAjaranId);
        }

        if (! $selectedTahunAjaran) {
            // Prioritaskan tahun ajaran yang sedang aktif, atau ambil item pertama
            $selectedTahunAjaran = $tahunAjaranList->firstWhere('is_active', true) ?? $tahunAjaranList->first();
            $selectedTahunAjaranId = $selectedTahunAjaran?->id ?? 0;
        }

        // 5. Daftar Semester untuk Tahun Ajaran terpilih
        $semesters = $selectedTahunAjaran
            ? $selectedTahunAjaran->semesters()->orderBy('semester')->get()
            : collect();

        // 6. Dapatkan semester yang saat ini aktif di sekolah tersebut
        $activeSemester = $selectedSchoolId > 0 ? Semester::activeFor($selectedSchoolId) : null;

        return view('konfigurasi-sekolah.tahun-ajaran.index', compact(
            'user',
            'schools',
            'selectedSchool',
            'selectedSchoolId',
            'tahunAjaranList',
            'selectedTahunAjaran',
            'selectedTahunAjaranId',
            'semesters',
            'activeSemester'
        ));
    }

    /**
     * Menyimpan data Tahun Ajaran baru.
     */
    public function store(StoreTahunAjaranRequest $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();
        $data = $request->validated();

        if ($user->school_id) {
            $data['school_id'] = $user->school_id;
        }

        $data['is_active'] = false;
        $tahunAjaran = TahunAjaran::create($data);

        return redirect()
            ->route('konfigurasi-sekolah.tahun-ajaran', $this->schoolRedirectParams($request, $tahunAjaran->school_id, [
                'tahun_ajaran_id' => $tahunAjaran->id,
            ]))
            ->with('success', "Tahun ajaran {$tahunAjaran->tahun} berhasil ditambahkan.");
    }

    /**
     * Memperbarui data Tahun Ajaran.
     */
    public function update(UpdateTahunAjaranRequest $request, TahunAjaran $tahunAjaran): RedirectResponse
    {
        $this->authorizeSchool($request, $tahunAjaran->school_id);

        /** @var User $user */
        $user = $request->user();

        $data = $request->validated();
        if ($user->school_id) {
            $data['school_id'] = $user->school_id;
        }

        $tahunAjaran->update($data);

        return redirect()
            ->route('konfigurasi-sekolah.tahun-ajaran', $this->schoolRedirectParams($request, $tahunAjaran->school_id, [
                'tahun_ajaran_id' => $tahunAjaran->id,
            ]))
            ->with('success', "Data tahun ajaran {$tahunAjaran->tahun} berhasil diperbarui.");
    }

    /**
     * Menetapkan suatu Tahun Ajaran sebagai tahun ajaran aktif tunggal pada sekolah tersebut.
     */
    public function activate(Request $request, TahunAjaran $tahunAjaran): RedirectResponse
    {
        $this->authorizeSchool($request, $tahunAjaran->school_id);

        $tahunAjaran->activate();

        $redirectParams = $this->schoolRedirectParams($request, $tahunAjaran->school_id, [
            'tahun_ajaran_id' => $tahunAjaran->id,
        ]);

        return redirect()
            ->route('konfigurasi-sekolah.tahun-ajaran', $redirectParams)
            ->with('success', "Tahun ajaran {$tahunAjaran->tahun} berhasil ditetapkan sebagai Tahun Ajaran Aktif.");
    }

    /**
     * Menonaktifkan tahun ajaran aktif (Khusus hak akses Super Administrator).
     */
    public function deactivate(Request $request, TahunAjaran $tahunAjaran): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        // Otorisasi: Hanya Super Administrator yang diizinkan menonaktifkan tahun ajaran aktif
        if (! $user->isSuperAdmin()) {
            abort(403, 'Hanya Super Administrator yang diizinkan menonaktifkan Tahun Ajaran aktif.');
        }

        $this->authorizeSchool($request, $tahunAjaran->school_id);

        $tahunAjaran->deactivate();

        $redirectParams = $this->schoolRedirectParams($request, $tahunAjaran->school_id, [
            'tahun_ajaran_id' => $tahunAjaran->id,
        ]);

        return redirect()
            ->route('konfigurasi-sekolah.tahun-ajaran', $redirectParams)
            ->with('success', "Tahun ajaran {$tahunAjaran->tahun} berhasil dinonaktifkan.");
    }

    /**
     * Menghapus Tahun Ajaran dengan proteksi keamanan.
     */
    public function destroy(Request $request, TahunAjaran $tahunAjaran): RedirectResponse
    {
        $this->authorizeSchool($request, $tahunAjaran->school_id);

        $schoolId = $tahunAjaran->school_id;
        $redirectParams = $this->schoolRedirectParams($request, $schoolId);

        // Proteksi 1: Tahun ajaran aktif dilarang dihapus
        if ($tahunAjaran->is_active) {
            return redirect()
                ->route('konfigurasi-sekolah.tahun-ajaran', $redirectParams)
                ->with('error', 'Tahun ajaran yang sedang berstatus AKTIF dilarang keras untuk dihapus.');
        }

        // Proteksi 2: Memiliki semester aktif
        if ($tahunAjaran->hasActiveSemester()) {
            return redirect()
                ->route('konfigurasi-sekolah.tahun-ajaran', $redirectParams)
                ->with('error', 'Tahun ajaran ini memiliki semester yang sedang aktif sehingga tidak dapat dihapus.');
        }

        // Proteksi 3: Keterikatan agenda kalender akademik
        $hasCalendarEvents = KalenderAkademik::query()
            ->where('school_id', $schoolId)
            ->where('tahun_ajaran', $tahunAjaran->tahun)
            ->exists();

        if ($hasCalendarEvents) {
            return redirect()
                ->route('konfigurasi-sekolah.tahun-ajaran', $redirectParams)
                ->with('error', 'Tahun ajaran ini sudah terikat dengan agenda kegiatan Kalender Akademik sehingga tidak dapat dihapus.');
        }

        $namaTahun = $tahunAjaran->tahun;
        $tahunAjaran->delete();

        return redirect()
            ->route('konfigurasi-sekolah.tahun-ajaran', $redirectParams)
            ->with('success', "Tahun ajaran {$namaTahun} beserta semesternya berhasil dihapus.");
    }

    /**
     * Menambahkan semester baru di bawah tahun ajaran terpilih.
     */
    public function storeSemester(StoreSemesterRequest $request, TahunAjaran $tahunAjaran): RedirectResponse
    {
        $this->authorizeSchool($request, $tahunAjaran->school_id);

        $data = $request->validated();
        $redirectParams = $this->schoolRedirectParams($request, $tahunAjaran->school_id, [
            'tahun_ajaran_id' => $tahunAjaran->id,
        ]);

        // Validasi duplikasi semester di tahun ajaran yang sama
        $alreadyExists = $tahunAjaran->semesters()
            ->where('semester', $data['semester'])
            ->exists();

        if ($alreadyExists) {
            $label = ucfirst($data['semester']);

            return redirect()
                ->route('konfigurasi-sekolah.tahun-ajaran', $redirectParams)
                ->with('error', "Semester {$label} sudah terdaftar untuk tahun ajaran {$tahunAjaran->tahun}.");
        }

        $semester = $tahunAjaran->semesters()->create($data);

        if (! empty($data['is_active'])) {
            $semester->activate();
        }

        return redirect()
            ->route('konfigurasi-sekolah.tahun-ajaran', $redirectParams)
            ->with('success', "Semester {$semester->label_semester} ({$tahunAjaran->tahun}) berhasil ditambahkan.");
    }

    /**
     * Memperbarui data semester.
     */
    public function updateSemester(UpdateSemesterRequest $request, TahunAjaran $tahunAjaran, Semester $semester): RedirectResponse
    {
        $this->authorizeSchool($request, $tahunAjaran->school_id);

        if ((int) $semester->tahun_ajaran_id !== (int) $tahunAjaran->id) {
            abort(404);
        }

        $data = $request->validated();
        $redirectParams = $this->schoolRedirectParams($request, $tahunAjaran->school_id, [
            'tahun_ajaran_id' => $tahunAjaran->id,
        ]);

        // Validasi duplikasi jika tipe semester diubah
        $duplicate = $tahunAjaran->semesters()
            ->where('semester', $data['semester'])
            ->where('id', '!=', $semester->id)
            ->exists();

        if ($duplicate) {
            $label = ucfirst($data['semester']);

            return redirect()
                ->route('konfigurasi-sekolah.tahun-ajaran', $redirectParams)
                ->with('error', "Semester {$label} sudah terdaftar untuk tahun ajaran {$tahunAjaran->tahun}.");
        }

        if (! empty($data['is_active'])) {
            $semester->update($data);
            $semester->activate();
        } else {
            $semester->update($data);
        }

        return redirect()
            ->route('konfigurasi-sekolah.tahun-ajaran', $redirectParams)
            ->with('success', "Data Semester {$semester->label_semester} berhasil diperbarui.");
    }

    /**
     * Menghapus semester dengan proteksi keamanan.
     */
    public function destroySemester(Request $request, TahunAjaran $tahunAjaran, Semester $semester): RedirectResponse
    {
        $this->authorizeSchool($request, $tahunAjaran->school_id);

        if ((int) $semester->tahun_ajaran_id !== (int) $tahunAjaran->id) {
            abort(404);
        }

        $schoolId = $tahunAjaran->school_id;
        $redirectParams = $this->schoolRedirectParams($request, $schoolId, [
            'tahun_ajaran_id' => $tahunAjaran->id,
        ]);

        // Proteksi 1: Semester aktif dilarang dihapus
        if ($semester->is_active) {
            return redirect()
                ->route('konfigurasi-sekolah.tahun-ajaran', $redirectParams)
                ->with('error', 'Semester yang sedang berstatus AKTIF dilarang keras untuk dihapus.');
        }

        // Proteksi 2: Keterikatan agenda kalender akademik
        $hasCalendarEvents = KalenderAkademik::query()
            ->where('school_id', $schoolId)
            ->where('tahun_ajaran', $tahunAjaran->tahun)
            ->where('semester', $semester->semester)
            ->exists();

        if ($hasCalendarEvents) {
            return redirect()
                ->route('konfigurasi-sekolah.tahun-ajaran', $redirectParams)
                ->with('error', "Semester {$semester->label_semester} sudah terikat dengan agenda kegiatan Kalender Akademik sehingga tidak dapat dihapus.");
        }

        $label = $semester->label_semester;
        $semester->delete();

        return redirect()
            ->route('konfigurasi-sekolah.tahun-ajaran', $redirectParams)
            ->with('success', "Semester {$label} berhasil dihapus.");
    }

    /**
     * Menetapkan suatu semester sebagai semester aktif tunggal pada sekolah tersebut.
     */
    public function activateSemester(Request $request, TahunAjaran $tahunAjaran, Semester $semester): RedirectResponse
    {
        $this->authorizeSchool($request, $tahunAjaran->school_id);

        if ((int) $semester->tahun_ajaran_id !== (int) $tahunAjaran->id) {
            abort(404);
        }

        $semester->activate();

        $redirectParams = $this->schoolRedirectParams($request, $tahunAjaran->school_id, [
            'tahun_ajaran_id' => $tahunAjaran->id,
        ]);

        return redirect()
            ->route('konfigurasi-sekolah.tahun-ajaran', $redirectParams)
            ->with('success', "Semester {$semester->label_semester} ({$tahunAjaran->tahun}) berhasil ditetapkan sebagai Semester Aktif.");
    }
}
