<?php

namespace App\Http\Controllers\KonfigurasiSekolah;

use App\Http\Controllers\Controller;
use App\Http\Requests\KonfigurasiSekolah\StoreSemesterRequest;
use App\Http\Requests\KonfigurasiSekolah\StoreTahunAjaranRequest;
use App\Http\Requests\KonfigurasiSekolah\UpdateSemesterRequest;
use App\Http\Requests\KonfigurasiSekolah\UpdateTahunAjaranRequest;
use App\Models\KalenderAkademik;
use App\Models\School;
use App\Models\Semester;
use App\Models\TahunAjaran;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class TahunAjaranController extends Controller
{
    /**
     * Menampilkan antarmuka master-detail konfigurasi Tahun Ajaran dan Semester.
     */
    public function index(Request $request): View
    {
        // 1. Ambil daftar sekolah aktif untuk dropdown selector
        $schools = School::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'npsn', 'school_type']);

        // 2. Tentukan sekolah terpilih (default: sekolah pertama)
        $selectedSchoolId = (int) $request->query('school_id', $schools->first()?->id ?? 0);
        $selectedSchool = $schools->firstWhere('id', $selectedSchoolId) ?? $schools->first();
        if ($selectedSchool && $selectedSchoolId !== $selectedSchool->id) {
            $selectedSchoolId = $selectedSchool->id;
        }

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

        return view('konfigurasi-sekolah.tahun-ajaran.index', [
            'schools' => $schools,
            'selectedSchool' => $selectedSchool,
            'selectedSchoolId' => $selectedSchoolId,
            'tahunAjaranList' => $tahunAjaranList,
            'selectedTahunAjaran' => $selectedTahunAjaran,
            'selectedTahunAjaranId' => $selectedTahunAjaranId,
            'semesters' => $semesters,
            'activeSemester' => $activeSemester,
        ]);
    }

    /**
     * Menyimpan data Tahun Ajaran baru.
     */
    public function store(StoreTahunAjaranRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $tahunAjaran = DB::transaction(function () use ($data) {
            if (! empty($data['is_active'])) {
                TahunAjaran::where('school_id', $data['school_id'])->update(['is_active' => false]);
            }

            return TahunAjaran::create($data);
        });

        return redirect()
            ->route('konfigurasi-sekolah.tahun-ajaran', [
                'school_id' => $tahunAjaran->school_id,
                'tahun_ajaran_id' => $tahunAjaran->id,
            ])
            ->with('success', "Tahun ajaran {$tahunAjaran->tahun} berhasil ditambahkan.");
    }

    /**
     * Memperbarui data Tahun Ajaran.
     */
    public function update(UpdateTahunAjaranRequest $request, TahunAjaran $tahunAjaran): RedirectResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($tahunAjaran, $data) {
            if (! empty($data['is_active'])) {
                TahunAjaran::where('school_id', $data['school_id'])
                    ->where('id', '!=', $tahunAjaran->id)
                    ->update(['is_active' => false]);
            }

            $tahunAjaran->update($data);
        });

        return redirect()
            ->route('konfigurasi-sekolah.tahun-ajaran', [
                'school_id' => $tahunAjaran->school_id,
                'tahun_ajaran_id' => $tahunAjaran->id,
            ])
            ->with('success', "Data tahun ajaran {$tahunAjaran->tahun} berhasil diperbarui.");
    }

    /**
     * Menghapus Tahun Ajaran dengan proteksi keamanan.
     */
    public function destroy(TahunAjaran $tahunAjaran): RedirectResponse
    {
        $schoolId = $tahunAjaran->school_id;

        // Proteksi 1: Tahun ajaran aktif dilarang dihapus
        if ($tahunAjaran->is_active) {
            return redirect()
                ->route('konfigurasi-sekolah.tahun-ajaran', ['school_id' => $schoolId])
                ->with('error', 'Tahun ajaran yang sedang berstatus AKTIF dilarang keras untuk dihapus.');
        }

        // Proteksi 2: Memiliki semester aktif
        if ($tahunAjaran->hasActiveSemester()) {
            return redirect()
                ->route('konfigurasi-sekolah.tahun-ajaran', ['school_id' => $schoolId])
                ->with('error', 'Tahun ajaran ini memiliki semester yang sedang aktif sehingga tidak dapat dihapus.');
        }

        // Proteksi 3: Keterikatan agenda kalender akademik
        $hasCalendarEvents = KalenderAkademik::query()
            ->where('school_id', $schoolId)
            ->where('tahun_ajaran', $tahunAjaran->tahun)
            ->exists();

        if ($hasCalendarEvents) {
            return redirect()
                ->route('konfigurasi-sekolah.tahun-ajaran', ['school_id' => $schoolId])
                ->with('error', 'Tahun ajaran ini sudah terikat dengan agenda kegiatan Kalender Akademik sehingga tidak dapat dihapus.');
        }

        $namaTahun = $tahunAjaran->tahun;
        $tahunAjaran->delete();

        return redirect()
            ->route('konfigurasi-sekolah.tahun-ajaran', ['school_id' => $schoolId])
            ->with('success', "Tahun ajaran {$namaTahun} beserta semesternya berhasil dihapus.");
    }

    /**
     * Menambahkan semester baru di bawah tahun ajaran terpilih.
     */
    public function storeSemester(StoreSemesterRequest $request, TahunAjaran $tahunAjaran): RedirectResponse
    {
        $data = $request->validated();

        // Validasi duplikasi semester di tahun ajaran yang sama
        $alreadyExists = $tahunAjaran->semesters()
            ->where('semester', $data['semester'])
            ->exists();

        if ($alreadyExists) {
            $label = ucfirst($data['semester']);

            return redirect()
                ->route('konfigurasi-sekolah.tahun-ajaran', [
                    'school_id' => $tahunAjaran->school_id,
                    'tahun_ajaran_id' => $tahunAjaran->id,
                ])
                ->with('error', "Semester {$label} sudah terdaftar untuk tahun ajaran {$tahunAjaran->tahun}.");
        }

        $semester = $tahunAjaran->semesters()->create($data);

        if (! empty($data['is_active'])) {
            $semester->activate();
        }

        return redirect()
            ->route('konfigurasi-sekolah.tahun-ajaran', [
                'school_id' => $tahunAjaran->school_id,
                'tahun_ajaran_id' => $tahunAjaran->id,
            ])
            ->with('success', "Semester {$semester->label_semester} ({$tahunAjaran->tahun}) berhasil ditambahkan.");
    }

    /**
     * Memperbarui data semester.
     */
    public function updateSemester(UpdateSemesterRequest $request, TahunAjaran $tahunAjaran, Semester $semester): RedirectResponse
    {
        $data = $request->validated();

        // Validasi duplikasi jika tipe semester diubah
        $duplicate = $tahunAjaran->semesters()
            ->where('semester', $data['semester'])
            ->where('id', '!=', $semester->id)
            ->exists();

        if ($duplicate) {
            $label = ucfirst($data['semester']);

            return redirect()
                ->route('konfigurasi-sekolah.tahun-ajaran', [
                    'school_id' => $tahunAjaran->school_id,
                    'tahun_ajaran_id' => $tahunAjaran->id,
                ])
                ->with('error', "Semester {$label} sudah terdaftar untuk tahun ajaran {$tahunAjaran->tahun}.");
        }

        if (! empty($data['is_active'])) {
            $semester->update($data);
            $semester->activate();
        } else {
            $semester->update($data);
        }

        return redirect()
            ->route('konfigurasi-sekolah.tahun-ajaran', [
                'school_id' => $tahunAjaran->school_id,
                'tahun_ajaran_id' => $tahunAjaran->id,
            ])
            ->with('success', "Data Semester {$semester->label_semester} berhasil diperbarui.");
    }

    /**
     * Menghapus semester dengan proteksi keamanan.
     */
    public function destroySemester(TahunAjaran $tahunAjaran, Semester $semester): RedirectResponse
    {
        $schoolId = $tahunAjaran->school_id;

        // Proteksi 1: Semester aktif dilarang dihapus
        if ($semester->is_active) {
            return redirect()
                ->route('konfigurasi-sekolah.tahun-ajaran', [
                    'school_id' => $schoolId,
                    'tahun_ajaran_id' => $tahunAjaran->id,
                ])
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
                ->route('konfigurasi-sekolah.tahun-ajaran', [
                    'school_id' => $schoolId,
                    'tahun_ajaran_id' => $tahunAjaran->id,
                ])
                ->with('error', "Semester {$semester->label_semester} sudah terikat dengan agenda kegiatan Kalender Akademik sehingga tidak dapat dihapus.");
        }

        $label = $semester->label_semester;
        $semester->delete();

        return redirect()
            ->route('konfigurasi-sekolah.tahun-ajaran', [
                'school_id' => $schoolId,
                'tahun_ajaran_id' => $tahunAjaran->id,
            ])
            ->with('success', "Semester {$label} berhasil dihapus.");
    }

    /**
     * Menetapkan suatu semester sebagai semester aktif tunggal pada sekolah tersebut.
     */
    public function activateSemester(TahunAjaran $tahunAjaran, Semester $semester): RedirectResponse
    {
        $semester->activate();

        return redirect()
            ->route('konfigurasi-sekolah.tahun-ajaran', [
                'school_id' => $tahunAjaran->school_id,
                'tahun_ajaran_id' => $tahunAjaran->id,
            ])
            ->with('success', "Semester {$semester->label_semester} ({$tahunAjaran->tahun}) berhasil ditetapkan sebagai Semester Aktif.");
    }
}
