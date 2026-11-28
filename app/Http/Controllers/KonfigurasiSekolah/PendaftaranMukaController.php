<?php

namespace App\Http\Controllers\KonfigurasiSekolah;

use App\Http\Controllers\Controller;
use App\Http\Requests\FaceRecognition\StoreFaceRegistrationRequest;
use App\Models\Kelas;
use App\Models\Murid;
use App\Models\MuridFaceProfile;
use App\Support\ImageUploader;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PendaftaranMukaController extends Controller
{
    /**
     * Tampilkan halaman manajemen pendaftaran wajah peserta didik beserta statistik KPI.
     */
    public function index(Request $request): View
    {
        [$selectedSchool, $selectedSchoolId, $schools] = $this->resolveSchoolContext($request);

        $baseQuery = Murid::query()
            ->where('school_id', $selectedSchoolId)
            ->aktif();

        $totalMuridAktif = (clone $baseQuery)->count();
        $sudahTerdaftarCount = (clone $baseQuery)->whereHas('activeFaceProfiles')->count();
        $belumTerdaftarCount = (clone $baseQuery)->whereDoesntHave('activeFaceProfiles')->count();
        $totalSampelAktif = MuridFaceProfile::query()
            ->where('school_id', $selectedSchoolId)
            ->aktif()
            ->whereHas('murid', fn (Builder $q) => $q->where('is_active', true))
            ->count();

        $query = $this->buildFilteredQuery($request, $selectedSchoolId);

        $perPageInput = (string) $request->query('per_page', '15');
        if ($perPageInput === 'semua' || $perPageInput === 'all') {
            $perPage = 1000;
        } elseif (in_array($perPageInput, ['15', '30', '90'], true)) {
            $perPage = (int) $perPageInput;
        } else {
            $perPage = 15;
            $perPageInput = '15';
        }

        $murids = $query->paginate($perPage)->withQueryString();

        $kelasList = Kelas::query()
            ->where('school_id', $selectedSchoolId)
            ->aktif()
            ->orderBy('nama')
            ->get();

        $muridOptions = Murid::query()
            ->with('kelas:id,nama')
            ->where('school_id', $selectedSchoolId)
            ->aktif()
            ->orderBy('nama_lengkap')
            ->get(['id', 'kelas_id', 'nama_lengkap', 'nisn', 'nis'])
            ->map(function (Murid $murid): array {
                $kelasLabel = $murid->kelas?->nama ?: 'Tanpa Kelas';
                $nomorInduk = $murid->nisn ?: $murid->nis;

                return [
                    'id' => $murid->id,
                    'nama' => $murid->nama_lengkap,
                    'kode' => $nomorInduk ? "{$kelasLabel} • {$nomorInduk}" : $kelasLabel,
                ];
            })
            ->values();

        return view('konfigurasi-sekolah.pendaftaran-wajah.index', compact(
            'murids',
            'totalMuridAktif',
            'sudahTerdaftarCount',
            'belumTerdaftarCount',
            'totalSampelAktif',
            'selectedSchool',
            'selectedSchoolId',
            'schools',
            'kelasList',
            'muridOptions',
            'perPageInput'
        ));
    }

    /**
     * Simpan sampel foto dan vektor biometrik wajah baru untuk peserta didik.
     */
    public function store(StoreFaceRegistrationRequest $request): RedirectResponse
    {
        $data = $request->validated();

        /** @var Murid $murid */
        $murid = Murid::query()->with('user')->findOrFail($data['murid_id']);
        $this->authorizeSchool($request, $murid->school_id);

        $fotoPath = ImageUploader::uploadBase64(
            $data['foto_base64'],
            'faces/murid',
            'face_murid',
            $request->user()?->id
        );

        if (! $fotoPath) {
            return redirect()
                ->route('konfigurasi-sekolah.regis-wajah.index', $this->schoolRedirectParams($request, $murid->school_id))
                ->with('error', 'Gagal memproses berkas foto wajah. Pastikan ukuran foto tidak melebihi 1MB.');
        }

        $descriptor = array_map(
            static fn ($val): float => round((float) $val, 6),
            $data['face_descriptor']
        );

        MuridFaceProfile::create([
            'school_id' => $murid->school_id,
            'murid_id' => $murid->id,
            'label' => $data['label'],
            'foto_path' => $fotoPath,
            'face_descriptor' => $descriptor,
            'confidence' => isset($data['confidence']) ? round((float) $data['confidence'], 4) : null,
            'model_version' => MuridFaceProfile::DEFAULT_MODEL_VERSION,
            'is_active' => true,
            'registered_by' => $request->user()?->id,
        ]);

        MuridFaceProfile::clearDescriptorCache($murid->school_id);

        // Sinkronkan ke pasfoto murid dan avatar akun secara terpisah agar penghapusan sampel tidak memutus avatar
        if (! empty($data['sync_murid_foto']) && empty($murid->foto_path)) {
            $avatarPath = ImageUploader::uploadBase64(
                $data['foto_base64'],
                'photos/avatar',
                'murid',
                $request->user()?->id
            );

            if ($avatarPath) {
                $murid->update(['foto_path' => $avatarPath]);

                if ($murid->user && empty($murid->user->avatar_path)) {
                    $murid->user->update(['avatar_path' => $avatarPath]);
                }
            }
        }

        return redirect()
            ->route('konfigurasi-sekolah.regis-wajah.index', $this->schoolRedirectParams($request, $murid->school_id))
            ->with('success', "Sampel wajah ({$data['label']}) untuk murid {$murid->nama_lengkap} berhasil didaftarkan.");
    }

    /**
     * Kembalikan daftar sampel wajah milik seorang murid dalam format JSON untuk modal kelola.
     */
    public function show(Request $request, Murid $murid): JsonResponse
    {
        $this->authorizeSchool($request, $murid->school_id);

        $murid->loadMissing([
            'school:id,name',
            'kelas.tingkatan',
            'kelas.jurusan',
            'faceProfiles' => fn ($query) => $query->with('registeredBy:id,name')->latest('id'),
        ]);

        return response()->json([
            'id' => $murid->id,
            'school_id' => $murid->school_id,
            'school_name' => $murid->school?->name,
            'nama_lengkap' => $murid->nama_lengkap,
            'nisn' => $murid->nisn ?: '-',
            'nis' => $murid->nis ?: '-',
            'kelas_nama' => $murid->kelas?->nama ?: 'Tanpa Kelas',
            'foto_url' => $murid->foto_url,
            'max_samples' => MuridFaceProfile::MAX_SAMPLES_PER_MURID,
            'total_count' => $murid->faceProfiles->count(),
            'active_count' => $murid->faceProfiles->where('is_active', true)->count(),
            'profiles' => $murid->faceProfiles->map(function (MuridFaceProfile $profile): array {
                return [
                    'id' => $profile->id,
                    'label' => $profile->label,
                    'foto_url' => $profile->foto_url,
                    'confidence' => $profile->confidence,
                    'confidence_percent' => $profile->confidence !== null ? round($profile->confidence * 100, 1).'%' : '-',
                    'model_version' => $profile->model_version,
                    'is_active' => (bool) $profile->is_active,
                    'registered_by_name' => $profile->registeredBy?->name ?: 'Sistem',
                    'created_at_formatted' => $profile->created_at?->translatedFormat('d M Y, H:i') ?: '-',
                    'toggle_url' => route('konfigurasi-sekolah.regis-wajah.toggle-status', $profile),
                    'delete_url' => route('konfigurasi-sekolah.regis-wajah.destroy', $profile),
                ];
            })->values(),
        ]);
    }

    /**
     * Alihkan status keaktifan sampel wajah (aktif/nonaktif) tanpa menghapus datanya.
     */
    public function toggleStatus(Request $request, MuridFaceProfile $faceProfile): RedirectResponse|JsonResponse
    {
        $this->authorizeSchool($request, $faceProfile->school_id);

        $faceProfile->loadMissing('murid:id,nama_lengkap');
        $faceProfile->update(['is_active' => ! $faceProfile->is_active]);
        MuridFaceProfile::clearDescriptorCache($faceProfile->school_id);

        $statusText = $faceProfile->is_active ? 'diaktifkan' : 'dinonaktifkan';
        $message = "Sampel wajah {$faceProfile->label} milik {$faceProfile->murid?->nama_lengkap} berhasil {$statusText}.";

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
                'is_active' => (bool) $faceProfile->is_active,
            ]);
        }

        return redirect()
            ->route('konfigurasi-sekolah.regis-wajah.index', $this->schoolRedirectParams($request, $faceProfile->school_id))
            ->with('success', $message);
    }

    /**
     * Hapus sampel wajah beserta berkas fotonya dari penyimpanan.
     */
    public function destroy(Request $request, MuridFaceProfile $faceProfile): RedirectResponse|JsonResponse
    {
        $this->authorizeSchool($request, $faceProfile->school_id);

        $faceProfile->loadMissing('murid:id,nama_lengkap');
        $schoolId = $faceProfile->school_id;
        $label = $faceProfile->label;
        $muridName = $faceProfile->murid?->nama_lengkap ?: 'Murid';

        ImageUploader::delete($faceProfile->foto_path);
        $faceProfile->delete();
        MuridFaceProfile::clearDescriptorCache($schoolId);

        $message = "Sampel wajah {$label} milik {$muridName} berhasil dihapus.";

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
            ]);
        }

        return redirect()
            ->route('konfigurasi-sekolah.regis-wajah.index', $this->schoolRedirectParams($request, $schoolId))
            ->with('success', $message);
    }

    /**
     * Bangun query daftar murid aktif beserta relasi profil wajah sesuai filter request.
     *
     * @return Builder<Murid>
     */
    protected function buildFilteredQuery(Request $request, int $selectedSchoolId): Builder
    {
        $query = Murid::query()
            ->with([
                'kelas.tingkatan',
                'kelas.jurusan',
                'faceProfiles' => fn ($q) => $q->latest('id'),
            ])
            ->withCount(['faceProfiles', 'activeFaceProfiles'])
            ->where('school_id', $selectedSchoolId)
            ->aktif()
            ->orderBy('nama_lengkap');

        if ($request->filled('search')) {
            $search = trim((string) $request->query('search'));
            $query->where(function (Builder $q) use ($search): void {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                    ->orWhere('nisn', 'like', "%{$search}%")
                    ->orWhere('nis', 'like', "%{$search}%");
            });
        }

        if ($request->filled('kelas_id')) {
            $kelasFilter = (string) $request->query('kelas_id');
            if ($kelasFilter === 'tanpa_kelas') {
                $query->whereNull('kelas_id');
            } elseif (is_numeric($kelasFilter)) {
                $query->where('kelas_id', (int) $kelasFilter);
            }
        }

        if ($request->filled('face_status')) {
            $faceStatus = (string) $request->query('face_status');
            if ($faceStatus === 'terdaftar') {
                $query->whereHas('activeFaceProfiles');
            } elseif ($faceStatus === 'belum') {
                $query->whereDoesntHave('activeFaceProfiles');
            }
        }

        return $query;
    }
}
