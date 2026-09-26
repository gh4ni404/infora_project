<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Http\Requests\Master\StoreJurusanRequest;
use App\Http\Requests\Master\UpdateJurusanRequest;
use App\Models\Jurusan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class JurusanController extends Controller
{
    /**
     * Tampilkan daftar master data jurusan beserta kartu metrik dan filter.
     */
    public function index(Request $request): View
    {
        // 1 & 2. Tentukan sekolah terpilih (konsisten dengan pendekatan multi-tenancy)
        [$selectedSchool, $selectedSchoolId, $schools] = $this->resolveSchoolContext($request);

        // 3. Query dasar per unit sekolah
        $baseQuery = Jurusan::query()->where('school_id', $selectedSchoolId);

        // 4. Hitung metrik statistik ringkasan (KPI)
        $totalCount = (clone $baseQuery)->count();
        $activeCount = (clone $baseQuery)->where('is_active', true)->count();
        $inactiveCount = $totalCount - $activeCount;
        $bidangCount = (clone $baseQuery)
            ->whereNotNull('bidang_keahlian')
            ->where('bidang_keahlian', '!=', '')
            ->distinct('bidang_keahlian')
            ->count('bidang_keahlian');

        // 5. Query data tabel dengan filter pencarian dan status
        $query = (clone $baseQuery)->orderBy('kode');

        if ($request->filled('search')) {
            $search = trim((string) $request->query('search'));
            $query->where(function ($q) use ($search) {
                $q->where('kode', 'like', "%{$search}%")
                    ->orWhere('nama', 'like', "%{$search}%")
                    ->orWhere('singkatan', 'like', "%{$search}%")
                    ->orWhere('bidang_keahlian', 'like', "%{$search}%")
                    ->orWhere('program_keahlian', 'like', "%{$search}%")
                    ->orWhere('kepala_jurusan', 'like', "%{$search}%");
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

        $jurusans = $query->paginate($perPage)->withQueryString();

        // 6. Daftar rekomendasi standar Bidang dan Program Keahlian untuk datalist
        $recommendedBidang = [
            'Teknologi Informasi',
            'Teknologi Manufaktur dan Rekayasa',
            'Teknologi Konstruksi dan Properti',
            'Bisnis dan Manajemen',
            'Seni dan Ekonomi Kreatif',
            'Kesehatan dan Pekerjaan Sosial',
            'Kemaritiman',
            'Agribisnis dan Agriteknologi',
            'Pariwisata',
            'Energi dan Pertambangan',
        ];

        $recommendedProgram = [
            'Pengembangan Perangkat Lunak dan Gim',
            'Teknik Jaringan Komputer dan Telekomunikasi',
            'Teknik Otomotif',
            'Teknik Elektronika',
            'Teknik Mesin',
            'Desain Komunikasi Visual',
            'Akuntansi dan Keuangan Lembaga',
            'Manajemen Perkantoran dan Layanan Bisnis',
            'Pemasaran',
            'Usaha Layanan Wisata',
            'Kuliner',
            'Busana',
        ];

        return view('master.data-jurusan.index', compact(
            'schools',
            'selectedSchool',
            'selectedSchoolId',
            'jurusans',
            'perPageInput',
            'totalCount',
            'activeCount',
            'inactiveCount',
            'bidangCount',
            'recommendedBidang',
            'recommendedProgram'
        ));
    }

    /**
     * Simpan data jurusan baru ke dalam basis data.
     */
    public function store(StoreJurusanRequest $request): RedirectResponse
    {
        $data = $request->validated();
        if ($request->user()?->school_id) {
            $data['school_id'] = $request->user()->school_id;
        }

        $jurusan = Jurusan::create($data);

        return redirect()
            ->route('master.data-jurusan.index', $this->schoolRedirectParams($request, $jurusan->school_id))
            ->with('success', "Jurusan {$jurusan->kode} ({$jurusan->nama}) berhasil ditambahkan ke dalam sistem.");
    }

    /**
     * Perbarui data jurusan di dalam basis data.
     */
    public function update(UpdateJurusanRequest $request, Jurusan $jurusan): RedirectResponse
    {
        $this->authorizeSchool($request, $jurusan->school_id);

        $data = $request->validated();
        if ($request->user()?->school_id) {
            $data['school_id'] = $request->user()->school_id;
        }

        $jurusan->update($data);

        return redirect()
            ->route('master.data-jurusan.index', $this->schoolRedirectParams($request, $jurusan->school_id))
            ->with('success', "Data jurusan {$jurusan->kode} ({$jurusan->nama}) berhasil diperbarui.");
    }

    /**
     * Hapus data jurusan dari basis data.
     */
    public function destroy(Request $request, Jurusan $jurusan): RedirectResponse
    {
        $this->authorizeSchool($request, $jurusan->school_id);

        $schoolId = $jurusan->school_id;
        $label = "{$jurusan->kode} ({$jurusan->nama})";

        $jurusan->delete();

        return redirect()
            ->route('master.data-jurusan.index', $this->schoolRedirectParams($request, $schoolId))
            ->with('success', "Data jurusan {$label} berhasil dihapus dari sistem.");
    }

    /**
     * Ubah status aktif/non-aktif jurusan secara cepat.
     */
    public function toggleStatus(Request $request, Jurusan $jurusan): RedirectResponse
    {
        $this->authorizeSchool($request, $jurusan->school_id);

        $jurusan->update([
            'is_active' => ! $jurusan->is_active,
        ]);

        $statusText = $jurusan->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return redirect()
            ->route('master.data-jurusan.index', $this->schoolRedirectParams($request, $jurusan->school_id))
            ->with('success', "Status jurusan {$jurusan->kode} berhasil {$statusText}.");
    }
}
