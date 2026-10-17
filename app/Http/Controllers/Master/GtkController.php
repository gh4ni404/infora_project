<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Http\Requests\Master\ImportGtkRequest;
use App\Http\Requests\Master\StoreGtkRequest;
use App\Http\Requests\Master\UpdateGtkRequest;
use App\Models\Gtk;
use App\Models\MenuAccessTemplate;
use App\Models\Provinsi;
use App\Services\SpreadsheetService;
use App\Support\GtkAccountManager;
use App\Support\ImageUploader;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Symfony\Component\HttpFoundation\StreamedResponse;

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

    /**
     * Unduh template spreadsheet berkas Excel (.xlsx) untuk panduan impor data GTK.
     */
    public function template(SpreadsheetService $spreadsheet): StreamedResponse
    {
        $headers = [
            'Nama Lengkap*',
            'Gelar Depan',
            'Gelar Belakang',
            'Jenis Kelamin (L/P)*',
            'Status Kepegawaian*',
            'Jenis PTK (pisahkan koma jika jamak)*',
            'NIK',
            'NIP',
            'NUPTK',
            'Tempat Lahir',
            'Tanggal Lahir (YYYY-MM-DD)',
            'Agama',
            'Pendidikan Terakhir',
            'Jurusan Pendidikan',
            'No HP/WhatsApp',
            'Email',
            'Alamat Domisili',
        ];

        $sampleRows = [
            [
                'Budi Santoso',
                'Drs.',
                'M.Pd.',
                'L',
                'PNS',
                'Guru Mapel, Wali Kelas',
                '7308011205780001',
                '197805122005011002',
                '1234567890123456',
                'Watampone',
                '1978-05-12',
                'Islam',
                'S2',
                'Pendidikan Matematika',
                '081234567890',
                'budi.santoso@sekolah.sch.id',
                'Jl. Merdeka No. 12',
            ],
            [
                'Siti Rahmawati',
                '',
                'S.Kom.',
                'P',
                'Tenaga Honorer',
                'Tenaga Administrasi Sekolah, Laboran',
                '7308015508890002',
                '',
                '',
                'Makassar',
                '1989-08-15',
                'Islam',
                'S1',
                'Sistem Informasi',
                '085298765432',
                'siti.rahma@sekolah.sch.id',
                'Kompleks Puri Asri Blok B/5',
            ],
        ];

        return $spreadsheet->template(
            'Template_Import_GTK_INFORA.xlsx',
            $headers,
            $sampleRows,
            [
                'sheet_title' => 'Template GTK',
                'text_columns' => ['NIK', 'NIP', 'NUPTK', 'No HP/WhatsApp'],
            ]
        );
    }

    /**
     * Ekspor data GTK ke berkas spreadsheet Excel (.xlsx) adaptif terhadap filter aktif.
     */
    public function export(Request $request, SpreadsheetService $spreadsheet): StreamedResponse
    {
        [$selectedSchool, $selectedSchoolId] = $this->resolveSchoolContext($request);
        $query = $this->buildFilteredQuery($request, $selectedSchoolId);

        $headers = [
            'Nama Lengkap',
            'Gelar Depan',
            'Gelar Belakang',
            'Jenis Kelamin',
            'Status Kepegawaian',
            'Jenis PTK',
            'NIK',
            'NIP',
            'NUPTK',
            'Tempat Lahir',
            'Tanggal Lahir',
            'Agama',
            'Pendidikan Terakhir',
            'Jurusan Pendidikan',
            'No HP/WhatsApp',
            'Email',
            'Alamat Domisili',
            'Status Aktif',
        ];

        $rows = [];
        /** @var Gtk $gtk */
        foreach ($query->get() as $gtk) {
            $rows[] = [
                $gtk->nama_lengkap,
                $gtk->gelar_depan ?? '',
                $gtk->gelar_belakang ?? '',
                $gtk->jenis_kelamin,
                $gtk->status_kepegawaian,
                is_array($gtk->jenis_ptk) ? implode(', ', $gtk->jenis_ptk) : (string) $gtk->jenis_ptk,
                $gtk->nik ?? '',
                $gtk->nip ?? '',
                $gtk->nuptk ?? '',
                $gtk->tempat_lahir ?? '',
                $gtk->tanggal_lahir ? $gtk->tanggal_lahir->format('Y-m-d') : '',
                $gtk->agama ?? '',
                $gtk->pendidikan_terakhir ?? '',
                $gtk->jurusan_pendidikan ?? '',
                $gtk->no_hp ?? '',
                $gtk->email ?? '',
                $gtk->alamat_lengkap,
                $gtk->is_active ? 'Aktif' : 'Non-Aktif',
            ];
        }

        $schoolName = $selectedSchool ? Str::slug($selectedSchool->name, '_') : 'Sekolah';
        $filename = "Data_GTK_{$schoolName}_".date('Ymd_His').'.xlsx';

        return $spreadsheet->export(
            $filename,
            $headers,
            $rows,
            [
                'sheet_title' => 'Data GTK',
                'text_columns' => ['NIK', 'NIP', 'NUPTK', 'No HP/WhatsApp'],
            ]
        );
    }

    /**
     * Impor massal data GTK dari berkas spreadsheet (.xlsx, .xls, .csv).
     */
    public function import(ImportGtkRequest $request, SpreadsheetService $spreadsheet): RedirectResponse
    {
        [$selectedSchool, $selectedSchoolId] = $this->resolveSchoolContext($request);
        $this->authorizeSchool($request, $selectedSchoolId);

        $uploadedFile = $request->file('file');
        $rawRows = $spreadsheet->readRows($uploadedFile);

        if (empty($rawRows)) {
            return redirect()
                ->route('master.data-gtk', $this->schoolRedirectParams($request, $selectedSchoolId))
                ->with('error', 'Berkas spreadsheet kosong atau tidak berisi baris data yang dapat diproses.');
        }

        $inserted = 0;
        $updated = 0;
        $skipped = 0;

        foreach ($rawRows as $row) {
            $namaLengkap = trim((string) ($row['nama_lengkap'] ?? $row['nama'] ?? ''));

            if (empty($namaLengkap)) {
                $skipped++;

                continue;
            }

            $nik = preg_replace('/\D/', '', (string) ($row['nik'] ?? '')) ?: null;
            $nip = preg_replace('/\D/', '', (string) ($row['nip'] ?? '')) ?: null;
            $nuptk = preg_replace('/\D/', '', (string) ($row['nuptk'] ?? '')) ?: null;

            $jenisKelamin = $this->parseJenisKelamin($row['jenis_kelamin'] ?? $row['jk'] ?? 'L');
            $statusKepegawaian = trim((string) ($row['status_kepegawaian'] ?? $row['status'] ?? 'GTT/PTT')) ?: 'GTT/PTT';
            $jenisPtk = $this->parseJenisPtk($row['jenis_ptk'] ?? $row['penugasan'] ?? 'Guru Mapel');

            $dataGtk = [
                'school_id' => $selectedSchoolId,
                'nama_lengkap' => $namaLengkap,
                'gelar_depan' => trim((string) ($row['gelar_depan'] ?? '')) ?: null,
                'gelar_belakang' => trim((string) ($row['gelar_belakang'] ?? '')) ?: null,
                'jenis_kelamin' => $jenisKelamin,
                'status_kepegawaian' => $statusKepegawaian,
                'jenis_ptk' => $jenisPtk,
                'nik' => $nik,
                'nip' => $nip,
                'nuptk' => $nuptk,
                'tempat_lahir' => trim((string) ($row['tempat_lahir'] ?? '')) ?: null,
                'tanggal_lahir' => $this->parseDate($row['tanggal_lahir'] ?? null),
                'agama' => trim((string) ($row['agama'] ?? '')) ?: null,
                'pendidikan_terakhir' => trim((string) ($row['pendidikan_terakhir'] ?? '')) ?: null,
                'jurusan_pendidikan' => trim((string) ($row['jurusan_pendidikan'] ?? '')) ?: null,
                'no_hp' => trim((string) ($row['no_hp'] ?? $row['no_hp_whatsapp'] ?? '')) ?: null,
                'email' => trim((string) ($row['email'] ?? '')) ?: null,
                'alamat' => trim((string) ($row['alamat_domisili'] ?? $row['alamat'] ?? '')) ?: null,
                'is_active' => true,
            ];

            // Strategi Upsert Cerdas: cari berdasarkan NIK atau NIP di sekolah ini
            $existingGtk = null;
            if ($nik) {
                $existingGtk = Gtk::where('school_id', $selectedSchoolId)->where('nik', $nik)->first();
            }
            if (! $existingGtk && $nip) {
                $existingGtk = Gtk::where('school_id', $selectedSchoolId)->where('nip', $nip)->first();
            }

            if ($existingGtk) {
                // Perbarui field yang terisi
                $existingGtk->update(array_filter($dataGtk, fn ($v) => $v !== null));
                GtkAccountManager::createOrUpdateUserForGtk($existingGtk);
                $updated++;
            } else {
                // Tambahkan sebagai data baru
                $newGtk = Gtk::create($dataGtk);
                GtkAccountManager::createOrUpdateUserForGtk($newGtk);
                $inserted++;
            }
        }

        $summaryMessage = "Impor data GTK selesai: {$inserted} data baru ditambahkan, {$updated} data diperbarui";
        if ($skipped > 0) {
            $summaryMessage .= ", {$skipped} baris dilewati karena data tidak lengkap.";
        } else {
            $summaryMessage .= '.';
        }

        return redirect()
            ->route('master.data-gtk', $this->schoolRedirectParams($request, $selectedSchoolId))
            ->with('success', $summaryMessage);
    }

    /**
     * Bangun query data GTK berdasarkan filter request yang aktif.
     */
    protected function buildFilteredQuery(Request $request, int $selectedSchoolId): Builder
    {
        $query = Gtk::query()
            ->with(['user', 'school', 'provinsi', 'kabupaten', 'kecamatan', 'kelurahan'])
            ->where('school_id', $selectedSchoolId)
            ->orderBy('nama_lengkap');

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

        return $query;
    }

    /**
     * Parsing tanggal lahir dari berbagai format masukan spreadsheet.
     */
    protected function parseDate(mixed $value): ?string
    {
        if (blank($value)) {
            return null;
        }

        if (is_numeric($value) && (float) $value > 1000) {
            try {
                return Date::excelToDateTimeObject((float) $value)->format('Y-m-d');
            } catch (\Throwable) {
                // Lanjut ke parser string standar
            }
        }

        try {
            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Parsing teks penugasan PTK majemuk menjadi array.
     *
     * @return array<int, string>
     */
    protected function parseJenisPtk(mixed $value): array
    {
        if (blank($value)) {
            return ['Guru Mapel'];
        }

        if (is_array($value)) {
            return array_values(array_filter($value));
        }

        $items = preg_split('/[,;]+/', (string) $value);
        $cleanItems = array_values(array_filter(array_map('trim', (array) $items)));

        return ! empty($cleanItems) ? $cleanItems : ['Guru Mapel'];
    }

    /**
     * Normalisasi nilai jenis kelamin menjadi L atau P.
     */
    protected function parseJenisKelamin(mixed $value): string
    {
        $val = strtoupper(trim((string) $value));

        if (str_starts_with($val, 'P') || str_starts_with($val, 'W')) {
            return 'P';
        }

        return 'L';
    }
}
