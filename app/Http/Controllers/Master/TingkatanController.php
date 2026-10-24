<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Http\Requests\Master\StoreTingkatanRequest;
use App\Http\Requests\Master\UpdateTingkatanRequest;
use App\Models\Tingkatan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TingkatanController extends Controller
{
    /**
     * Tampilkan daftar master data tingkatan beserta kartu metrik dan filter.
     */
    public function index(Request $request): View
    {
        // 1 & 2. Tentukan sekolah terpilih (konsisten dengan arsitektur multi-tenancy)
        [$selectedSchool, $selectedSchoolId, $schools] = $this->resolveSchoolContext($request);

        // 3. Query dasar per unit sekolah
        $baseQuery = Tingkatan::query()->where('school_id', $selectedSchoolId);

        // 4. Hitung metrik statistik ringkasan (KPI)
        $totalCount = (clone $baseQuery)->count();
        $activeCount = (clone $baseQuery)->where('is_active', true)->count();
        $inactiveCount = $totalCount - $activeCount;
        $faseCount = (clone $baseQuery)
            ->whereNotNull('fase')
            ->where('fase', '!=', '')
            ->distinct('fase')
            ->count('fase');

        // 5. Query data tabel dengan filter pencarian dan status
        $query = (clone $baseQuery)->ordered();

        if ($request->filled('search')) {
            $search = trim((string) $request->query('search'));
            $query->where(function ($q) use ($search) {
                $q->where('kode', 'like', "%{$search}%")
                    ->orWhere('nama', 'like', "%{$search}%")
                    ->orWhere('fase', 'like', "%{$search}%")
                    ->orWhere('keterangan', 'like', "%{$search}%");

                if (is_numeric($search)) {
                    $q->orWhere('tingkat_angka', (int) $search);
                }
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

        $tingkatans = $query->paginate($perPage)->withQueryString();

        return view('master.data-tingkatan.index', compact(
            'schools',
            'selectedSchool',
            'selectedSchoolId',
            'tingkatans',
            'perPageInput',
            'totalCount',
            'activeCount',
            'inactiveCount',
            'faseCount'
        ));
    }

    /**
     * Simpan data tingkatan baru ke dalam basis data.
     */
    public function store(StoreTingkatanRequest $request): RedirectResponse
    {
        $data = $request->validated();
        if ($request->user()?->school_id) {
            $data['school_id'] = $request->user()->school_id;
        }

        $tingkatan = Tingkatan::create($data);

        return redirect()
            ->route('master.data-tingkatan.index', $this->schoolRedirectParams($request, $tingkatan->school_id))
            ->with('success', "Tingkatan {$tingkatan->kode} ({$tingkatan->nama}) berhasil ditambahkan ke dalam sistem.");
    }

    /**
     * Perbarui data tingkatan di dalam basis data.
     */
    public function update(UpdateTingkatanRequest $request, Tingkatan $tingkatan): RedirectResponse
    {
        $this->authorizeSchool($request, $tingkatan->school_id);

        $data = $request->validated();
        if ($request->user()?->school_id) {
            $data['school_id'] = $request->user()->school_id;
        }

        $tingkatan->update($data);

        return redirect()
            ->route('master.data-tingkatan.index', $this->schoolRedirectParams($request, $tingkatan->school_id))
            ->with('success', "Data tingkatan {$tingkatan->kode} ({$tingkatan->nama}) berhasil diperbarui.");
    }

    /**
     * Hapus data tingkatan dari basis data.
     */
    public function destroy(Request $request, Tingkatan $tingkatan): RedirectResponse
    {
        $this->authorizeSchool($request, $tingkatan->school_id);

        $schoolId = $tingkatan->school_id;
        $label = "{$tingkatan->kode} ({$tingkatan->nama})";

        $tingkatan->delete();

        return redirect()
            ->route('master.data-tingkatan.index', $this->schoolRedirectParams($request, $schoolId))
            ->with('success', "Data tingkatan {$label} berhasil dihapus dari sistem.");
    }

    /**
     * Ubah status aktif/non-aktif tingkatan secara cepat.
     */
    public function toggleStatus(Request $request, Tingkatan $tingkatan): RedirectResponse
    {
        $this->authorizeSchool($request, $tingkatan->school_id);

        $tingkatan->update([
            'is_active' => ! $tingkatan->is_active,
        ]);

        $statusText = $tingkatan->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return redirect()
            ->route('master.data-tingkatan.index', $this->schoolRedirectParams($request, $tingkatan->school_id))
            ->with('success', "Status tingkatan {$tingkatan->kode} berhasil {$statusText}.");
    }
}
