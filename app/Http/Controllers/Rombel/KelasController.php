<?php

namespace App\Http\Controllers\Rombel;

use App\Http\Controllers\Controller;
use App\Http\Requests\Rombel\StoreKelasRequest;
use App\Http\Requests\Rombel\UpdateKelasRequest;
use App\Models\Gtk;
use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\TahunAjaran;
use App\Models\Tingkatan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KelasController extends Controller
{
    /**
     * Tampilkan daftar rombongan belajar / kelas beserta kartu metrik dan filter.
     */
    public function index(Request $request): View
    {
        // 1 & 2. Tentukan sekolah terpilih (konsisten dengan arsitektur multi-tenancy)
        [$selectedSchool, $selectedSchoolId, $schools] = $this->resolveSchoolContext($request);

        // Ambil daftar seluruh tahun ajaran untuk sekolah terpilih
        $tahunAjarans = TahunAjaran::query()
            ->where('school_id', $selectedSchoolId)
            ->orderByDesc('tahun')
            ->get();

        $activeTahunAjaran = $tahunAjarans->firstWhere('is_active', true) ?? $tahunAjarans->first();

        // Filter Tahun Ajaran: default mengacu pada Tahun Ajaran yang aktif
        $selectedTahunAjaranId = null;
        if ($request->has('tahun_ajaran_id')) {
            $rawTa = $request->query('tahun_ajaran_id');
            if ($rawTa !== 'semua' && $rawTa !== '' && is_numeric($rawTa)) {
                $selectedTahunAjaranId = (int) $rawTa;
            }
        } else {
            $selectedTahunAjaranId = $activeTahunAjaran?->id;
        }

        // 3. Query dasar per unit sekolah
        $baseQuery = Kelas::query()->where('school_id', $selectedSchoolId);

        if ($selectedTahunAjaranId) {
            $baseQuery->where('tahun_ajaran_id', $selectedTahunAjaranId);
        }

        // 4. Hitung metrik statistik ringkasan (KPI Cards)
        $totalCount = (clone $baseQuery)->count();
        $activeCount = (clone $baseQuery)->where('is_active', true)->count();
        $inactiveCount = $totalCount - $activeCount;
        $totalKapasitas = (int) (clone $baseQuery)->where('is_active', true)->sum('kapasitas');
        $assignedWaliCount = (clone $baseQuery)->whereNotNull('wali_kelas_id')->count();

        // 5. Query data tabel dengan eager loading dan filter
        $query = (clone $baseQuery)->with(['tahunAjaran', 'tingkatan', 'jurusan', 'waliKelas'])->latest();

        if ($request->filled('search')) {
            $search = trim((string) $request->query('search'));
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                    ->orWhere('kode', 'like', "%{$search}%")
                    ->orWhere('ruangan', 'like', "%{$search}%")
                    ->orWhereHas('waliKelas', function ($wq) use ($search) {
                        $wq->where('nama_lengkap', 'like', "%{$search}%")
                            ->orWhere('nip', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('tingkatan_id')) {
            $tingkatanFilter = $request->query('tingkatan_id');
            if (is_numeric($tingkatanFilter)) {
                $query->where('tingkatan_id', (int) $tingkatanFilter);
            }
        }

        if ($request->filled('jurusan_id')) {
            $jurusanFilter = $request->query('jurusan_id');
            if ($jurusanFilter === 'umum') {
                $query->whereNull('jurusan_id');
            } elseif (is_numeric($jurusanFilter)) {
                $query->where('jurusan_id', (int) $jurusanFilter);
            }
        }

        if ($request->filled('status')) {
            $status = $request->query('status');
            if ($status === 'aktif') {
                $query->where('is_active', true);
            } elseif ($status === 'nonaktif') {
                $query->where('is_active', false);
            }
        }

        // Paginasi dinamis (15, 30, 90, semua)
        $perPageInput = $request->query('per_page', '15');
        if ($perPageInput === 'semua' || $perPageInput === 'all') {
            $perPage = 1000;
        } elseif (in_array($perPageInput, ['15', '30', '90'], true)) {
            $perPage = (int) $perPageInput;
        } else {
            $perPage = 15;
            $perPageInput = '15';
        }

        $kelasList = $query->paginate($perPage)->withQueryString();

        // Data referensi untuk filter dan modal form
        $tingkatans = Tingkatan::query()
            ->where('school_id', $selectedSchoolId)
            ->aktif()
            ->ordered()
            ->get();

        $jurusans = Jurusan::query()
            ->where('school_id', $selectedSchoolId)
            ->aktif()
            ->orderBy('nama')
            ->get();

        $gtks = Gtk::query()
            ->where('school_id', $selectedSchoolId)
            ->where('is_active', true)
            ->orderBy('nama_lengkap')
            ->get(['id', 'nama_lengkap', 'gelar_depan', 'gelar_belakang', 'nip']);

        // Peta penugasan wali kelas untuk soft indicator (GTK ID => Nama Kelas)
        $assignedWaliMap = Kelas::query()
            ->where('school_id', $selectedSchoolId)
            ->when($selectedTahunAjaranId, fn ($q) => $q->where('tahun_ajaran_id', $selectedTahunAjaranId))
            ->whereNotNull('wali_kelas_id')
            ->pluck('nama', 'wali_kelas_id')
            ->all();

        return view('rombel.kelas.index', compact(
            'schools',
            'selectedSchool',
            'selectedSchoolId',
            'tahunAjarans',
            'activeTahunAjaran',
            'selectedTahunAjaranId',
            'kelasList',
            'tingkatans',
            'jurusans',
            'gtks',
            'assignedWaliMap',
            'perPageInput',
            'totalCount',
            'activeCount',
            'inactiveCount',
            'totalKapasitas',
            'assignedWaliCount'
        ));
    }

    /**
     * Simpan data kelas baru ke dalam basis data.
     */
    public function store(StoreKelasRequest $request): RedirectResponse
    {
        $data = $request->validated();
        if ($request->user()?->school_id) {
            $data['school_id'] = $request->user()->school_id;
        }

        $kelas = Kelas::create($data);

        return redirect()
            ->route('rombel.kelas.index', $this->schoolRedirectParams($request, $kelas->school_id, [
                'tahun_ajaran_id' => $kelas->tahun_ajaran_id,
            ]))
            ->with('success', "Data kelas {$kelas->nama} berhasil ditambahkan ke dalam sistem.");
    }

    /**
     * Perbarui data kelas di dalam basis data.
     */
    public function update(UpdateKelasRequest $request, Kelas $kelas): RedirectResponse
    {
        $this->authorizeSchool($request, $kelas->school_id);

        $data = $request->validated();
        if ($request->user()?->school_id) {
            $data['school_id'] = $request->user()->school_id;
        }

        $kelas->update($data);

        return redirect()
            ->route('rombel.kelas.index', $this->schoolRedirectParams($request, $kelas->school_id, [
                'tahun_ajaran_id' => $kelas->tahun_ajaran_id,
            ]))
            ->with('success', "Data kelas {$kelas->nama} berhasil diperbarui.");
    }

    /**
     * Hapus data kelas dari basis data.
     */
    public function destroy(Request $request, Kelas $kelas): RedirectResponse
    {
        $this->authorizeSchool($request, $kelas->school_id);

        $schoolId = $kelas->school_id;
        $tahunAjaranId = $kelas->tahun_ajaran_id;
        $nama = $kelas->nama;

        $kelas->delete();

        return redirect()
            ->route('rombel.kelas.index', $this->schoolRedirectParams($request, $schoolId, [
                'tahun_ajaran_id' => $tahunAjaranId,
            ]))
            ->with('success', "Data kelas {$nama} berhasil dihapus dari sistem.");
    }

    /**
     * Ubah status aktif/non-aktif kelas secara cepat.
     */
    public function toggleStatus(Request $request, Kelas $kelas): RedirectResponse
    {
        $this->authorizeSchool($request, $kelas->school_id);

        $kelas->update([
            'is_active' => ! $kelas->is_active,
        ]);

        $statusText = $kelas->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return redirect()
            ->route('rombel.kelas.index', $this->schoolRedirectParams($request, $kelas->school_id, [
                'tahun_ajaran_id' => $kelas->tahun_ajaran_id,
            ]))
            ->with('success', "Status kelas {$kelas->nama} berhasil {$statusText}.");
    }
}
