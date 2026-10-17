<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Http\Requests\Master\StoreGtkRequest;
use App\Http\Requests\Master\UpdateGtkRequest;
use App\Models\Gtk;
use App\Models\MenuAccessTemplate;
use App\Models\Provinsi;
use App\Support\GtkAccountManager;
use App\Support\ImageUploader;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GtkController extends Controller
{
    /**
     * Tampilkan daftar master data GTK beserta metrik KPI dan filter pencarian.
     */
    public function index(Request $request): View
    {
        // 1 & 2. Tentukan sekolah terpilih (konsisten dengan pendekatan multi-tenancy)
        [$selectedSchool, $selectedSchoolId, $schools] = $this->resolveSchoolContext($request);

        // 3. Query dasar per unit sekolah
        $baseQuery = Gtk::query()
            ->with(['user', 'school', 'provinsi', 'kabupaten', 'kecamatan', 'kelurahan'])
            ->where('school_id', $selectedSchoolId);

        // 4. Hitung metrik statistik ringkasan (KPI)
        $totalCount = (clone $baseQuery)->count();
        $pendidikCount = (clone $baseQuery)->pendidik()->count();
        $tendikCount = (clone $baseQuery)->tendik()->count();
        $activeCount = (clone $baseQuery)->where('is_active', true)->count();
        $inactiveCount = $totalCount - $activeCount;

        // 5. Query data tabel dengan filter pencarian dan kriteria
        $query = (clone $baseQuery)->orderBy('nama_lengkap');

        if ($request->filled('search')) {
            $search = trim((string) $request->query('search'));
            $query->where(function ($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                    ->orWhere('nip', 'like', "%{$search}%")
                    ->orWhere('nuptk', 'like', "%{$search}%")
                    ->orWhere('nik', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('no_hp', 'like', "%{$search}%");
            });
        }

        if ($request->filled('jenis_ptk')) {
            $selectedJenis = (string) $request->query('jenis_ptk');
            $query->where(function ($q) use ($selectedJenis) {
                $q->whereJsonContains('jenis_ptk', $selectedJenis)
                    ->orWhere('jenis_ptk', 'like', "%{$selectedJenis}%");
            });
        }

        if ($request->filled('status_kepegawaian')) {
            $query->where('status_kepegawaian', (string) $request->query('status_kepegawaian'));
        }

        if ($request->filled('status')) {
            $status = (string) $request->query('status');
            if ($status === 'aktif') {
                $query->where('is_active', true);
            } elseif ($status === 'nonaktif') {
                $query->where('is_active', false);
            }
        }

        // Paginasi (15, 30, 90, semua)
        $perPageInput = (string) $request->query('per_page', '15');
        if ($perPageInput === 'semua' || $perPageInput === 'all') {
            $perPage = 1000;
        } elseif (in_array($perPageInput, ['15', '30', '90'], true)) {
            $perPage = (int) $perPageInput;
        } else {
            $perPage = 15;
            $perPageInput = '15';
        }

        $gtks = $query->paginate($perPage)->withQueryString();

        // 6. Data referensi standar untuk formulir dan filter
        $jenisPtkPendidik = [
            'Guru Mata Pelajaran',
            'Wali Kelas',
            'Pembimbing PKL / Magang',
            'Guru BK / Konselor',
            'Guru Kelas',
            'Guru Pendamping Khusus',
            'Kepala Program Keahlian (Kaprog)',
            'Kepala Laboratorium / Bengkel',
            'Pembina OSIS / Ekstrakurikuler',
            'Tim Penjamin Mutu / Akreditasi',
            'Kepala Sekolah',
        ];

        $jenisPtkTendik = [
            'Tenaga Administrasi Sekolah (Staf TU)',
            'Kepala Tata Usaha (KTU)',
            'Bendahara Sekolah',
            'Laboran / Teknisi Lab',
            'Pustakawan',
            'Teknisi IT / Operator Dapodik',
            'Petugas Keamanan',
            'Petugas Kebersihan',
        ];

        $jenisPtkList = array_merge($jenisPtkPendidik, $jenisPtkTendik);

        $statusKepegawaianList = [
            'PNS',
            'PPPK',
            'GTY/PTY (Tetap Yayasan)',
            'GTT/PTT (Tidak Tetap)',
            'Honorer Sekolah',
        ];

        $pendidikanList = [
            'SMA / SMK',
            'Diploma (D3)',
            'Sarjana / Diploma IV (S1/D4)',
            'Magister (S2)',
            'Doktor (S3)',
        ];

        $roleTemplates = MenuAccessTemplate::query()
            ->select('role_key', 'role_name', 'role_category')
            ->distinct()
            ->whereIn('role_category', ['guru', 'staf'])
            ->get();

        $provinsiList = Provinsi::query()
            ->aktif()
            ->orderBy('kode')
            ->get(['id', 'kode', 'nama']);

        return view('master.data-gtk.index', compact(
            'schools',
            'selectedSchool',
            'selectedSchoolId',
            'gtks',
            'perPageInput',
            'totalCount',
            'pendidikCount',
            'tendikCount',
            'activeCount',
            'inactiveCount',
            'jenisPtkPendidik',
            'jenisPtkTendik',
            'jenisPtkList',
            'statusKepegawaianList',
            'pendidikanList',
            'roleTemplates',
            'provinsiList'
        ));
    }

    /**
     * Simpan data GTK baru dan otomatis buatkan akun pengguna terkait.
     */
    public function store(StoreGtkRequest $request): RedirectResponse
    {
        $data = $request->validated();

        if ($request->user()?->school_id) {
            $data['school_id'] = $request->user()->school_id;
        }

        if (! empty($data['foto'])) {
            $data['foto_path'] = ImageUploader::uploadBase64($data['foto'], 'avatars/gtk', 'gtk');
        }
        unset($data['foto']);

        $credentials = [
            'username' => $data['username'] ?? null,
            'password' => $data['password'] ?? null,
            'role_template' => $data['role_template'] ?? null,
        ];
        unset($data['username'], $data['password'], $data['role_template']);

        $gtk = Gtk::create($data);

        // Buat akun pengguna login terkait
        $user = GtkAccountManager::createOrUpdateUserForGtk($gtk, $credentials);

        $passwordNote = filled($credentials['password'])
            ? 'kata sandi kustom yang ditentukan'
            : 'kata sandi bawaan: '.GtkAccountManager::resolveDefaultPassword($gtk);

        return redirect()
            ->route('master.data-gtk', $this->schoolRedirectParams($request, $gtk->school_id))
            ->with('success', "Data GTK {$gtk->nama_bergelar} dan akun pengguna ({$user->username}) berhasil ditambahkan ke sistem ({$passwordNote}).");
    }

    /**
     * Tampilkan detail lengkap GTK (respons JSON untuk modal pratinjau detail).
     */
    public function show(Request $request, Gtk $gtk): JsonResponse
    {
        $this->authorizeSchool($request, $gtk->school_id);

        $gtk->load(['user', 'school', 'provinsi', 'kabupaten', 'kecamatan', 'kelurahan']);

        return response()->json([
            'id' => $gtk->id,
            'school_name' => $gtk->school?->name,
            'nama_lengkap' => $gtk->nama_lengkap,
            'gelar_depan' => $gtk->gelar_depan,
            'gelar_belakang' => $gtk->gelar_belakang,
            'nama_bergelar' => $gtk->nama_bergelar,
            'jenis_kelamin' => $gtk->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan',
            'nik' => $gtk->nik ?: '-',
            'nip' => $gtk->nip ?: '-',
            'nuptk' => $gtk->nuptk ?: '-',
            'jenis_ptk' => is_array($gtk->jenis_ptk) ? implode(', ', $gtk->jenis_ptk) : ($gtk->jenis_ptk ?: '-'),
            'jenis_ptk_list' => (array) ($gtk->jenis_ptk ?? []),
            'status_kepegawaian' => $gtk->status_kepegawaian,
            'tempat_lahir' => $gtk->tempat_lahir ?: '-',
            'tanggal_lahir' => $gtk->tanggal_lahir ? $gtk->tanggal_lahir->translatedFormat('d F Y') : '-',
            'agama' => $gtk->agama ?: '-',
            'no_hp' => $gtk->no_hp ?: '-',
            'email' => $gtk->email ?: '-',
            'provinsi_id' => $gtk->provinsi_id,
            'kabupaten_id' => $gtk->kabupaten_id,
            'kecamatan_id' => $gtk->kecamatan_id,
            'kelurahan_id' => $gtk->kelurahan_id,
            'provinsi_nama' => $gtk->provinsi?->nama,
            'kabupaten_nama' => $gtk->kabupaten?->nama,
            'kecamatan_nama' => $gtk->kecamatan?->nama,
            'kelurahan_nama' => $gtk->kelurahan?->nama,
            'alamat' => $gtk->alamat ?: '-',
            'alamat_lengkap' => $gtk->alamat_lengkap,
            'pendidikan_terakhir' => $gtk->pendidikan_terakhir ?: '-',
            'jurusan_pendidikan' => $gtk->jurusan_pendidikan ?: '-',
            'foto_url' => $gtk->foto_path ? asset('storage/'.$gtk->foto_path) : null,
            'is_active' => (bool) $gtk->is_active,
            'username' => $gtk->user?->username ?: '-',
            'user_type' => $gtk->user?->user_type ?: '-',
        ]);
    }

    /**
     * Perbarui data GTK dan sinkronisasikan informasi akun pengguna terkait.
     */
    public function update(UpdateGtkRequest $request, Gtk $gtk): RedirectResponse
    {
        $this->authorizeSchool($request, $gtk->school_id);

        $data = $request->validated();

        if ($request->user()?->school_id) {
            $data['school_id'] = $request->user()->school_id;
        }

        if (! empty($data['foto'])) {
            if ($gtk->foto_path) {
                ImageUploader::delete($gtk->foto_path);
            }
            $data['foto_path'] = ImageUploader::uploadBase64($data['foto'], 'avatars/gtk', 'gtk');
        }
        unset($data['foto']);

        $credentials = [
            'username' => $data['username'] ?? null,
            'password' => $data['password'] ?? null,
            'role_template' => $data['role_template'] ?? null,
        ];
        unset($data['username'], $data['password'], $data['role_template']);

        $gtk->update($data);

        // Sinkronisasi data akun login
        GtkAccountManager::createOrUpdateUserForGtk($gtk, $credentials);

        return redirect()
            ->route('master.data-gtk', $this->schoolRedirectParams($request, $gtk->school_id))
            ->with('success', "Data GTK {$gtk->nama_bergelar} berhasil diperbarui.");
    }

    /**
     * Hapus data GTK (Soft Delete) dan nonaktifkan akun pengguna terkait.
     */
    public function destroy(Request $request, Gtk $gtk): RedirectResponse
    {
        $this->authorizeSchool($request, $gtk->school_id);

        $schoolId = $gtk->school_id;
        $label = $gtk->nama_bergelar;

        // Nonaktifkan akun user login terkait
        if ($gtk->user) {
            $gtk->user->update(['is_active' => false]);
        }

        $gtk->delete();

        return redirect()
            ->route('master.data-gtk', $this->schoolRedirectParams($request, $schoolId))
            ->with('success', "Data GTK {$label} berhasil dihapus dari sistem (akun pengguna telah dinonaktifkan).");
    }

    /**
     * Ubah status aktif/nonaktif GTK secara cepat dan sinkronkan akun login.
     */
    public function toggleStatus(Request $request, Gtk $gtk): RedirectResponse
    {
        $this->authorizeSchool($request, $gtk->school_id);

        $newStatus = ! $gtk->is_active;

        $gtk->update(['is_active' => $newStatus]);

        if ($gtk->user) {
            $gtk->user->update(['is_active' => $newStatus]);
        }

        $statusText = $newStatus ? 'diaktifkan' : 'dinonaktifkan';

        return redirect()
            ->route('master.data-gtk', $this->schoolRedirectParams($request, $gtk->school_id))
            ->with('success', "Status GTK {$gtk->nama_bergelar} beserta akun pengguna berhasil {$statusText}.");
    }

    /**
     * Reset kata sandi akun login GTK kembali ke nilai default (NIP/NUPTK/Username).
     */
    public function resetPassword(Request $request, Gtk $gtk): RedirectResponse
    {
        $this->authorizeSchool($request, $gtk->school_id);

        $newPassword = GtkAccountManager::resetPassword($gtk);

        if (! $newPassword) {
            return redirect()
                ->route('master.data-gtk', $this->schoolRedirectParams($request, $gtk->school_id))
                ->with('error', "Gagal mereset kata sandi: Akun pengguna untuk {$gtk->nama_bergelar} tidak ditemukan.");
        }

        return redirect()
            ->route('master.data-gtk', $this->schoolRedirectParams($request, $gtk->school_id))
            ->with('success', "Kata sandi akun {$gtk->nama_bergelar} berhasil direset ke nilai bawaan: {$newPassword}");
    }
}
