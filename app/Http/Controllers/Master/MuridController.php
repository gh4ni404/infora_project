<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Http\Requests\Master\ImportMuridRequest;
use App\Http\Requests\Master\StoreMuridRequest;
use App\Http\Requests\Master\UpdateMuridRequest;
use App\Models\Kelas;
use App\Models\MenuAccessTemplate;
use App\Models\Murid;
use App\Models\Provinsi;
use App\Models\Tingkatan;
use App\Services\SpreadsheetService;
use App\Support\MuridAccountManager;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MuridController extends Controller
{
    /**
     * Tampilkan daftar master data murid beserta kartu metrik KPI dan filter pencarian.
     */
    public function index(Request $request): View
    {
        // 1 & 2. Tentukan konteks unit sekolah aktif
        [$selectedSchool, $selectedSchoolId, $schools] = $this->resolveSchoolContext($request);

        // 3. Query dasar per unit sekolah
        $baseQuery = Murid::query()->where('school_id', $selectedSchoolId);

        // 4. Hitung metrik statistik ringkasan (KPI Cards)
        $totalCount = (clone $baseQuery)->count();
        $lakiCount = (clone $baseQuery)->where('jenis_kelamin', 'L')->count();
        $perempuanCount = (clone $baseQuery)->where('jenis_kelamin', 'P')->count();
        $activeCount = (clone $baseQuery)->where('is_active', true)->count();
        $tanpaKelasCount = (clone $baseQuery)->whereNull('kelas_id')->count();

        // 5. Query data tabel dengan filter pencarian dan eager loading
        $query = $this->buildFilteredQuery($request, $selectedSchoolId);

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

        $murids = $query->paginate($perPage)->withQueryString();

        // 6. Data referensi standar untuk formulir dan filter
        $kelasList = Kelas::query()
            ->where('school_id', $selectedSchoolId)
            ->aktif()
            ->orderBy('nama')
            ->get();

        $tingkatanList = Tingkatan::query()
            ->where('school_id', $selectedSchoolId)
            ->aktif()
            ->orderBy('tingkat_angka')
            ->get();

        $roleTemplates = MenuAccessTemplate::query()
            ->select('role_key', 'role_name', 'role_category')
            ->where('role_category', 'murid')
            ->distinct()
            ->get();

        // Data referensi provinsi untuk inisialisasi dropdown wilayah Kemendagri
        $provinsiList = Provinsi::query()
            ->aktif()
            ->orderBy('kode')
            ->get(['id', 'kode', 'nama']);

        return view('master.data-murid.index', compact(
            'murids',
            'totalCount',
            'lakiCount',
            'perempuanCount',
            'activeCount',
            'tanpaKelasCount',
            'selectedSchool',
            'selectedSchoolId',
            'schools',
            'kelasList',
            'tingkatanList',
            'roleTemplates',
            'provinsiList',
            'perPageInput'
        ));
    }

    /**
     * Simpan data murid baru dan otomatis buatkan akun pengguna terkait.
     */
    public function store(StoreMuridRequest $request): RedirectResponse
    {
        $data = $request->validated();

        if ($request->user()?->school_id) {
            $data['school_id'] = $request->user()->school_id;
        }

        unset($data['create_user_account']);

        $murid = Murid::create($data);

        $user = MuridAccountManager::createOrUpdateUserForMurid($murid);
        $cleanPassword = MuridAccountManager::resolveDefaultPassword($murid, null, $user->username);

        return redirect()
            ->route('master.data-murid', $this->schoolRedirectParams($request, $murid->school_id))
            ->with('success', "Data murid {$murid->nama_lengkap} berhasil ditambahkan. Akun login otomatis dibuat dengan Username: {$user->username} dan Kata Sandi: {$cleanPassword}");
    }

    /**
     * Tampilkan data detail profil murid dalam format JSON untuk modal preview.
     */
    public function show(Murid $murid): JsonResponse
    {
        $murid->loadMissing(['school', 'user', 'kelas.tingkatan', 'kelas.jurusan', 'provinsi', 'kabupaten', 'kecamatan', 'kelurahan']);

        return response()->json([
            'id' => $murid->id,
            'school_id' => $murid->school_id,
            'school_name' => $murid->school?->name,
            'nama_lengkap' => $murid->nama_lengkap,
            'nisn' => $murid->nisn ?: '-',
            'nis' => $murid->nis ?: '-',
            'nik' => $murid->nik ?: '-',
            'jenis_kelamin' => $murid->jenis_kelamin,
            'jenis_kelamin_label' => $murid->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan',
            'kelas_id' => $murid->kelas_id,
            'kelas_nama' => $murid->kelas?->nama ?: 'Belum Masuk Kelas',
            'tingkatan' => $murid->kelas?->tingkatan?->nama ?: '-',
            'jurusan' => $murid->kelas?->jurusan?->nama ?: '-',
            'angkatan' => $murid->angkatan ?: '-',
            'tempat_lahir' => $murid->tempat_lahir ?: '-',
            'tanggal_lahir' => $murid->tanggal_lahir?->format('Y-m-d'),
            'tanggal_lahir_formatted' => $murid->tanggal_lahir?->translatedFormat('d F Y') ?: '-',
            'agama' => $murid->agama ?: '-',
            'no_hp' => $murid->no_hp ?: '-',
            'email' => $murid->email ?: '-',
            'provinsi_id' => $murid->provinsi_id,
            'kabupaten_id' => $murid->kabupaten_id,
            'kecamatan_id' => $murid->kecamatan_id,
            'kelurahan_id' => $murid->kelurahan_id,
            'provinsi_nama' => $murid->provinsi?->nama,
            'kabupaten_nama' => $murid->kabupaten?->nama,
            'kecamatan_nama' => $murid->kecamatan?->nama,
            'kelurahan_nama' => $murid->kelurahan?->nama,
            'alamat' => $murid->alamat ?: '-',
            'alamat_lengkap' => $murid->alamat_lengkap ?: '-',
            'nama_ayah' => $murid->nama_ayah ?: '-',
            'nama_ibu' => $murid->nama_ibu ?: '-',
            'nama_wali' => $murid->nama_wali ?: '-',
            'no_hp_ortu' => $murid->no_hp_ortu ?: '-',
            'foto_url' => $murid->foto_url,
            'status' => $murid->status,
            'is_active' => (bool) $murid->is_active,
            'user_id' => $murid->user_id,
            'username' => $murid->user?->username ?: '-',
            'menu_access_url' => $murid->user_id ? route('sistem.user.user', $murid->user_id) : null,
        ]);
    }

    /**
     * Perbarui data murid dan sinkronisasikan informasi akun pengguna terkait.
     */
    public function update(UpdateMuridRequest $request, Murid $murid): RedirectResponse
    {
        $this->authorizeSchool($request, $murid->school_id);

        $data = $request->validated();

        if ($request->user()?->school_id) {
            $data['school_id'] = $request->user()->school_id;
        }

        $murid->update($data);

        if ($murid->user) {
            $murid->user->update([
                'name' => $murid->nama_lengkap,
                'email' => $murid->email,
                'is_active' => (bool) $murid->is_active,
            ]);
        }

        return redirect()
            ->route('master.data-murid', $this->schoolRedirectParams($request, $murid->school_id))
            ->with('success', "Data murid {$murid->nama_lengkap} berhasil diperbarui.");
    }

    /**
     * Hapus data murid (Soft Delete) dan nonaktifkan akun pengguna terkait.
     */
    public function destroy(Request $request, Murid $murid): RedirectResponse
    {
        $this->authorizeSchool($request, $murid->school_id);

        $schoolId = $murid->school_id;
        $label = $murid->nama_lengkap;

        if ($murid->user) {
            $murid->user->update(['is_active' => false]);
        }

        $murid->delete();

        return redirect()
            ->route('master.data-murid', $this->schoolRedirectParams($request, $schoolId))
            ->with('success', "Data murid {$label} berhasil dihapus dari sistem.");
    }

    /**
     * Alihkan status keaktifan murid secara cepat (1-klik toggle).
     */
    public function toggleStatus(Request $request, Murid $murid): RedirectResponse
    {
        $this->authorizeSchool($request, $murid->school_id);

        $murid->update(['is_active' => ! $murid->is_active]);

        if ($murid->user) {
            $murid->user->update(['is_active' => $murid->is_active]);
        }

        $statusText = $murid->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return redirect()
            ->route('master.data-murid', $this->schoolRedirectParams($request, $murid->school_id))
            ->with('success', "Status data murid {$murid->nama_lengkap} berhasil {$statusText}.");
    }

    /**
     * Reset kata sandi akun pengguna murid ke sandi bawaan sistem.
     */
    public function resetPassword(Request $request, Murid $murid): RedirectResponse
    {
        $this->authorizeSchool($request, $murid->school_id);

        if (! $murid->user) {
            // Jika belum punya akun, buatkan akun baru
            $user = MuridAccountManager::createOrUpdateUserForMurid($murid);
            $cleanPassword = MuridAccountManager::resolveDefaultPassword($murid, null, $user->username);

            return redirect()
                ->route('master.data-murid', $this->schoolRedirectParams($request, $murid->school_id))
                ->with('success', "Akun login baru untuk {$murid->nama_lengkap} berhasil dibuat dengan username: {$user->username} dan sandi default: {$cleanPassword}.");
        }

        $cleanPassword = MuridAccountManager::resetPassword($murid);

        return redirect()
            ->route('master.data-murid', $this->schoolRedirectParams($request, $murid->school_id))
            ->with('success', "Kata sandi akun murid {$murid->nama_lengkap} berhasil direset ke nilai bawaan: {$cleanPassword}.");
    }

    /**
     * Unduh template spreadsheet berkas Excel (.xlsx) untuk panduan impor data murid.
     */
    public function template(Request $request, SpreadsheetService $spreadsheet): StreamedResponse
    {
        [$selectedSchool, $selectedSchoolId] = $this->resolveSchoolContext($request);
        $isMadrasah = $selectedSchool?->isMadrasah() ?? false;

        $nisHeader = $isMadrasah ? 'NISM*' : 'NIS';
        $sampleNis1 = $isMadrasah ? '131273710001260001' : '24001';
        $sampleNis2 = $isMadrasah ? '131273710001260002' : '24002';

        $headers = [
            'Nama Lengkap*',
            'Jenis Kelamin (L/P)*',
            'NISN',
            $nisHeader,
            'Angkatan',
            'Email',
        ];

        $sampleRows = [
            [
                'Muhammad Raditya',
                'L',
                '0071234567',
                $sampleNis1,
                '2026',
                'raditya@example.com',
            ],
            [
                'Aisyah Putri Azzahra',
                'P',
                '0087654321',
                $sampleNis2,
                '2026',
                'aisyah@example.com',
            ],
        ];

        $referenceColumns = [
            'Pilihan Jenis Kelamin' => ['L', 'P'],
        ];

        if ($isMadrasah) {
            $referenceColumns['Ketentuan NISM (Madrasah)'] = [
                'Wajib terdiri tepat 18 digit angka',
                '12 digit: Nomor Statistik Madrasah (NSM)',
                '2 digit: Tahun masuk (contoh: 26)',
                '4 digit: Nomor urut murid (contoh: 0001)',
            ];
        }

        return $spreadsheet->template(
            'Template_Import_Data_Murid_INFORA.xlsx',
            $headers,
            $sampleRows,
            [
                'sheet_title' => 'Template Murid',
                'text_columns' => ['NISN', $nisHeader, 'NIS', 'NISM'],
                'validations' => [
                    'Jenis Kelamin (L/P)*' => [
                        'options' => ['L', 'P'],
                        'title' => 'Pilih Jenis Kelamin',
                        'prompt' => 'Pilih L (Laki-laki) atau P (Perempuan)',
                    ],
                ],
                'reference_sheet' => [
                    'title' => 'Panduan & Referensi',
                    'columns' => $referenceColumns,
                ],
            ]
        );
    }

    /**
     * Ekspor data murid ke berkas spreadsheet Excel (.xlsx) adaptif terhadap filter aktif.
     */
    public function export(Request $request, SpreadsheetService $spreadsheet): StreamedResponse
    {
        [$selectedSchool, $selectedSchoolId] = $this->resolveSchoolContext($request);
        $query = $this->buildFilteredQuery($request, $selectedSchoolId);

        $headers = [
            'Nama Lengkap',
            'Jenis Kelamin',
            'NISN',
            'NIS',
            'NIK',
            'Kelas / Rombel',
            'Tingkatan',
            'Jurusan',
            'Angkatan',
            'Tempat Lahir',
            'Tanggal Lahir',
            'Agama',
            'No HP Murid',
            'Email',
            'Nama Ayah',
            'Nama Ibu',
            'Nama Wali',
            'No HP Ortu / WA',
            'Alamat Lengkap',
            'Status Murid',
            'Status Akun',
            'Username Akun',
        ];

        $format = strtolower((string) $request->query('format', 'xlsx'));
        $filename = 'Data_Murid_'.($selectedSchool?->name ? str_replace(' ', '_', $selectedSchool->name) : 'INFORA').'_'.date('Ymd_His').'.'.$format;

        $rowsGenerator = function () use ($query) {
            foreach ($query->lazy(200) as $murid) {
                yield [
                    $murid->nama_lengkap,
                    $murid->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan',
                    $murid->nisn ?: '-',
                    $murid->nis ?: '-',
                    $murid->nik ?: '-',
                    $murid->kelas?->nama ?: 'Tanpa Kelas',
                    $murid->kelas?->tingkatan?->nama ?: '-',
                    $murid->kelas?->jurusan?->nama ?: '-',
                    $murid->angkatan ?: '-',
                    $murid->tempat_lahir ?: '-',
                    $murid->tanggal_lahir ? $murid->tanggal_lahir->format('Y-m-d') : '-',
                    $murid->agama ?: '-',
                    $murid->no_hp ?: '-',
                    $murid->email ?: '-',
                    $murid->nama_ayah ?: '-',
                    $murid->nama_ibu ?: '-',
                    $murid->nama_wali ?: '-',
                    $murid->no_hp_ortu ?: '-',
                    $murid->alamat_lengkap ?: '-',
                    ucfirst((string) $murid->status),
                    $murid->is_active ? 'Aktif' : 'Non-Aktif',
                    $murid->user?->username ?: '-',
                ];
            }
        };

        return $spreadsheet->export(
            $filename,
            $headers,
            $rowsGenerator(),
            [
                'format' => $format,
                'sheet_title' => 'Data Murid',
                'text_columns' => ['NISN', 'NIS', 'NIK', 'No HP Murid', 'No HP Ortu / WA'],
            ]
        );
    }

    /**
     * Impor massal data murid dari berkas spreadsheet (.xlsx, .xls, .csv).
     */
    public function import(ImportMuridRequest $request, SpreadsheetService $spreadsheet): RedirectResponse
    {
        [$selectedSchool, $selectedSchoolId] = $this->resolveSchoolContext($request);
        $this->authorizeSchool($request, $selectedSchoolId);

        $uploadedFile = $request->file('file');
        $rawRows = $spreadsheet->readRows($uploadedFile);

        if (empty($rawRows)) {
            return redirect()
                ->route('master.data-murid', $this->schoolRedirectParams($request, $selectedSchoolId))
                ->with('error', 'Berkas spreadsheet kosong atau tidak berisi baris data yang dapat diproses.');
        }

        $isMadrasah = $selectedSchool?->isMadrasah() ?? false;

        $inserted = 0;
        $updated = 0;
        $skipped = 0;
        $invalidNismCount = 0;

        foreach ($rawRows as $row) {
            $namaLengkap = trim((string) ($row['nama_lengkap'] ?? $row['nama'] ?? ''));

            if (empty($namaLengkap)) {
                $skipped++;

                continue;
            }

            $nisn = preg_replace('/\s+/', '', (string) ($row['nisn'] ?? '')) ?: null;
            if ($nisn !== null && ! preg_match('/^\d{1,10}$/', $nisn)) {
                $skipped++;

                continue;
            }

            $rawNis = (string) ($row['nism'] ?? $row['nis'] ?? '');
            $nis = preg_replace('/\s+/', '', $rawNis) ?: null;

            // Validasi aturan NIS / NISM berdasarkan tipe naungan sekolah
            if ($isMadrasah) {
                // Di madrasah Kemenag: NISM wajib diisi dan harus tepat 18 digit angka
                if ($nis === null || ! preg_match('/^\d{18}$/', $nis)) {
                    $skipped++;
                    $invalidNismCount++;

                    continue;
                }
            } else {
                // Di sekolah umum: jika diisi, maksimal 30 karakter
                if ($nis !== null && mb_strlen($nis) > 30) {
                    $skipped++;

                    continue;
                }
            }

            // Resolusi jenis kelamin (L atau P)
            $rawJk = strtoupper(trim((string) ($row['jenis_kelamin'] ?? $row['jk'] ?? 'L')));
            $jenisKelamin = str_starts_with($rawJk, 'P') ? 'P' : 'L';

            $dataMurid = [
                'school_id' => $selectedSchoolId,
                'nama_lengkap' => $namaLengkap,
                'jenis_kelamin' => $jenisKelamin,
                'nisn' => $nisn,
                'nis' => $nis,
                'angkatan' => trim((string) ($row['angkatan'] ?? '')) ?: null,
                'email' => trim((string) ($row['email'] ?? '')) ?: null,
                'status' => 'aktif',
                'is_active' => true,
            ];

            // Strategi Upsert Cerdas: cari berdasarkan NISN, NIS/NISM, atau Nama Lengkap
            $existingMurid = null;
            if ($nisn) {
                $existingMurid = Murid::where('school_id', $selectedSchoolId)->where('nisn', $nisn)->first();
            }
            if (! $existingMurid && $nis) {
                $existingMurid = Murid::where('school_id', $selectedSchoolId)->where('nis', $nis)->first();
            }
            if (! $existingMurid && ! $nisn && ! $nis) {
                $existingMurid = Murid::where('school_id', $selectedSchoolId)
                    ->whereRaw('LOWER(TRIM(nama_lengkap)) = ?', [strtolower($namaLengkap)])
                    ->first();
            }

            if ($existingMurid) {
                $existingMurid->update(array_filter($dataMurid, fn ($v) => $v !== null));
                MuridAccountManager::createOrUpdateUserForMurid($existingMurid);
                $updated++;
            } else {
                $newMurid = Murid::create($dataMurid);
                MuridAccountManager::createOrUpdateUserForMurid($newMurid);
                $inserted++;
            }
        }

        // Jika seluruh baris data ditolak / tidak dapat diproses
        if ($inserted === 0 && $updated === 0 && $skipped > 0) {
            $errorMessage = $invalidNismCount > 0
                ? "Gagal mengimpor data murid: {$skipped} baris dilewati karena format NISM madrasah tidak valid (wajib 18 digit angka)."
                : "Gagal mengimpor data murid: {$skipped} baris dilewati karena data tidak lengkap atau tidak valid.";

            return redirect()
                ->route('master.data-murid', $this->schoolRedirectParams($request, $selectedSchoolId))
                ->with('error', $errorMessage);
        }

        $summaryMessage = "Impor data murid selesai: {$inserted} data baru ditambahkan, {$updated} data diperbarui";
        if ($skipped > 0) {
            $reason = $invalidNismCount > 0
                ? "{$skipped} baris dilewati (termasuk {$invalidNismCount} baris dengan format NISM tidak valid; madrasah mewajibkan 18 digit angka)."
                : "{$skipped} baris dilewati karena format data tidak lengkap atau tidak valid.";
            $summaryMessage .= ", {$reason}";
        } else {
            $summaryMessage .= '.';
        }

        return redirect()
            ->route('master.data-murid', $this->schoolRedirectParams($request, $selectedSchoolId))
            ->with('success', $summaryMessage);
    }

    /**
     * Bangun query data murid berdasarkan filter request yang aktif.
     *
     * @return Builder<Murid>
     */
    protected function buildFilteredQuery(Request $request, int $selectedSchoolId): Builder
    {
        $query = Murid::query()
            ->with(['school', 'user', 'kelas.tingkatan', 'kelas.jurusan', 'provinsi', 'kabupaten', 'kecamatan', 'kelurahan'])
            ->where('school_id', $selectedSchoolId)
            ->latest('id');

        if ($request->filled('search')) {
            $search = trim((string) $request->query('search'));
            $query->where(function (Builder $q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                    ->orWhere('nisn', 'like', "%{$search}%")
                    ->orWhere('nis', 'like', "%{$search}%")
                    ->orWhere('nik', 'like', "%{$search}%")
                    ->orWhere('no_hp', 'like', "%{$search}%")
                    ->orWhere('no_hp_ortu', 'like', "%{$search}%");
            });
        }

        if ($request->filled('kelas_id')) {
            $kelasFilter = $request->query('kelas_id');
            if ($kelasFilter === 'tanpa_kelas') {
                $query->whereNull('kelas_id');
            } elseif (is_numeric($kelasFilter)) {
                $query->where('kelas_id', (int) $kelasFilter);
            }
        }

        if ($request->filled('tingkatan_id')) {
            $tingkatanFilter = $request->query('tingkatan_id');
            if (is_numeric($tingkatanFilter)) {
                $query->whereHas('kelas', fn ($q) => $q->where('tingkatan_id', (int) $tingkatanFilter));
            }
        }

        if ($request->filled('jenis_kelamin')) {
            $jk = strtoupper((string) $request->query('jenis_kelamin'));
            if (in_array($jk, ['L', 'P'], true)) {
                $query->where('jenis_kelamin', $jk);
            }
        }

        if ($request->filled('status')) {
            $status = (string) $request->query('status');
            if (in_array($status, ['aktif', 'lulus', 'mutasi', 'keluar', 'dropout'], true)) {
                $query->where('status', $status);
            }
        }

        if ($request->filled('keaktifan')) {
            $keaktifan = $request->query('keaktifan');
            if ($keaktifan === 'aktif') {
                $query->where('is_active', true);
            } elseif ($keaktifan === 'nonaktif') {
                $query->where('is_active', false);
            }
        }

        return $query;
    }
}
