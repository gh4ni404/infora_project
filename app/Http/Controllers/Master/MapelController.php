<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Http\Requests\Master\StoreMapelRequest;
use App\Http\Requests\Master\UpdateMapelRequest;
use App\Models\Jurusan;
use App\Models\Mapel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MapelController extends Controller
{
    /**
     * Tampilkan daftar master data mata pelajaran beserta kartu metrik dan filter.
     */
    public function index(Request $request): View
    {
        // 1 & 2. Tentukan sekolah terpilih (konsisten dengan arsitektur multi-tenancy)
        [$selectedSchool, $selectedSchoolId, $schools] = $this->resolveSchoolContext($request);

        // 3. Query dasar per unit sekolah
        $baseQuery = Mapel::query()->where('school_id', $selectedSchoolId);

        // 4. Hitung metrik statistik ringkasan (KPI)
        $totalCount = (clone $baseQuery)->count();
        $activeCount = (clone $baseQuery)->where('is_active', true)->count();
        $inactiveCount = $totalCount - $activeCount;
        $totalBebanJam = (int) (clone $baseQuery)->where('is_active', true)->sum('beban_jam');

        // 5. Query data tabel dengan filter pencarian, status, kelompok, dan jurusan
        $query = (clone $baseQuery)->with(['jurusan'])->latest();

        if ($request->filled('search')) {
            $search = trim((string) $request->query('search'));
            $query->where(function ($q) use ($search) {
                $q->where('kode', 'like', "%{$search}%")
                    ->orWhere('nama', 'like', "%{$search}%")
                    ->orWhere('singkatan', 'like', "%{$search}%")
                    ->orWhere('keterangan', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $status = $request->query('status');
            if ($status === 'aktif') {
                $query->where('is_active', true);
            } elseif ($status === 'nonaktif') {
                $query->where('is_active', false);
            }
        }

        if ($request->filled('kelompok')) {
            $kelompok = $request->query('kelompok');
            if (in_array($kelompok, ['Umum', 'Kejuruan / Peminatan', 'Muatan Lokal', 'Pilihan'], true)) {
                $query->where('kelompok', $kelompok);
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

        // Paginasi (15, 30, 90, semua)
        $perPageInput = $request->query('per_page', '15');
        if ($perPageInput === 'semua' || $perPageInput === 'all') {
            $perPage = 1000;
        } elseif (in_array($perPageInput, ['15', '30', '90'], true)) {
            $perPage = (int) $perPageInput;
        } else {
            $perPage = 15;
            $perPageInput = '15';
        }

        $mapels = $query->paginate($perPage)->withQueryString();

        // Daftar jurusan aktif untuk unit sekolah yang dipilih
        $jurusans = Jurusan::query()
            ->where('school_id', $selectedSchoolId)
            ->aktif()
            ->orderBy('nama')
            ->get();

        return view('master.data-mapel.index', compact(
            'schools',
            'selectedSchool',
            'selectedSchoolId',
            'mapels',
            'jurusans',
            'perPageInput',
            'totalCount',
            'activeCount',
            'inactiveCount',
            'totalBebanJam'
        ));
    }

    /**
     * Simpan data mata pelajaran baru ke dalam basis data.
     */
    public function store(StoreMapelRequest $request): RedirectResponse
    {
        $data = $request->validated();
        if ($request->user()?->school_id) {
            $data['school_id'] = $request->user()->school_id;
        }

        $mapel = Mapel::create($data);

        return redirect()
            ->route('master.data-mapel.index', $this->schoolRedirectParams($request, $mapel->school_id))
            ->with('success', "Mata Pelajaran {$mapel->kode} ({$mapel->nama}) berhasil ditambahkan ke dalam sistem.");
    }

    /**
     * Perbarui data mata pelajaran di dalam basis data.
     */
    public function update(UpdateMapelRequest $request, Mapel $mapel): RedirectResponse
    {
        $this->authorizeSchool($request, $mapel->school_id);

        $data = $request->validated();
        if ($request->user()?->school_id) {
            $data['school_id'] = $request->user()->school_id;
        }

        $mapel->update($data);

        return redirect()
            ->route('master.data-mapel.index', $this->schoolRedirectParams($request, $mapel->school_id))
            ->with('success', "Data mata pelajaran {$mapel->kode} ({$mapel->nama}) berhasil diperbarui.");
    }

    /**
     * Hapus data mata pelajaran dari basis data.
     */
    public function destroy(Request $request, Mapel $mapel): RedirectResponse
    {
        $this->authorizeSchool($request, $mapel->school_id);

        $schoolId = $mapel->school_id;
        $label = "{$mapel->kode} ({$mapel->nama})";

        $mapel->delete();

        return redirect()
            ->route('master.data-mapel.index', $this->schoolRedirectParams($request, $schoolId))
            ->with('success', "Data mata pelajaran {$label} berhasil dihapus dari sistem.");
    }

    /**
     * Ubah status aktif/non-aktif mata pelajaran secara cepat.
     */
    public function toggleStatus(Request $request, Mapel $mapel): RedirectResponse
    {
        $this->authorizeSchool($request, $mapel->school_id);

        $mapel->update([
            'is_active' => ! $mapel->is_active,
        ]);

        $statusText = $mapel->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return redirect()
            ->route('master.data-mapel.index', $this->schoolRedirectParams($request, $mapel->school_id))
            ->with('success', "Status mata pelajaran {$mapel->kode} berhasil {$statusText}.");
    }
}
