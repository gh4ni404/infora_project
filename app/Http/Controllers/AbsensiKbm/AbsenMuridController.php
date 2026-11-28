<?php

namespace App\Http\Controllers\AbsensiKbm;

use App\Http\Controllers\Controller;
use App\Http\Requests\FaceRecognition\StoreAbsenMuridRequest;
use App\Models\AbsensiMurid;
use App\Models\Kelas;
use App\Models\Murid;
use App\Models\MuridFaceProfile;
use App\Models\Semester;
use App\Models\TahunAjaran;
use App\Support\ImageUploader;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class AbsenMuridController extends Controller
{
    /**
     * Tampilkan halaman Kiosk Absen Murid berbasis Face Recognition dan rekap harian.
     */
    public function index(Request $request): View
    {
        [$selectedSchool, $selectedSchoolId, $schools] = $this->resolveSchoolContext($request);

        $rawDate = (string) $request->query('tanggal', today()->toDateString());
        $selectedDate = preg_match('/^\d{4}-\d{2}-\d{2}$/', $rawDate) === 1
            ? $rawDate
            : today()->toDateString();

        $stats = $this->computeDailyStats($selectedSchoolId, $selectedDate);

        $query = $this->buildFilteredQuery($request, $selectedSchoolId, $selectedDate);

        $perPageInput = (string) $request->query('per_page', '15');
        if ($perPageInput === 'semua' || $perPageInput === 'all') {
            $perPage = 1000;
        } elseif (in_array($perPageInput, ['15', '30', '90'], true)) {
            $perPage = (int) $perPageInput;
        } else {
            $perPage = 15;
            $perPageInput = '15';
        }

        $absensis = $query->paginate($perPage)->withQueryString();

        $recentAttendances = AbsensiMurid::query()
            ->with(['murid:id,nama_lengkap,nisn,nis,foto_path', 'kelas:id,nama'])
            ->where('school_id', $selectedSchoolId)
            ->padaTanggal(today()->toDateString())
            ->orderByDesc('updated_at')
            ->limit(6)
            ->get();

        $kelasList = Kelas::query()
            ->where('school_id', $selectedSchoolId)
            ->aktif()
            ->orderBy('nama')
            ->get();

        return view('absensi-kbm.deteksi-wajah.index', compact(
            'absensis',
            'recentAttendances',
            'stats',
            'selectedDate',
            'selectedSchool',
            'selectedSchoolId',
            'schools',
            'kelasList',
            'perPageInput'
        ));
    }

    /**
     * Kembalikan daftar descriptor wajah murid aktif dalam JSON untuk pencocokan lokal di browser.
     */
    public function descriptors(Request $request): JsonResponse
    {
        [$selectedSchool, $selectedSchoolId] = $this->resolveSchoolContext($request);
        $this->authorizeSchool($request, $selectedSchoolId);

        $kelasId = $request->query('kelas_id');
        $kelasFilter = is_numeric($kelasId) ? (int) $kelasId : null;
        $today = today()->toDateString();

        $cacheKey = MuridFaceProfile::descriptorCacheKey($selectedSchoolId, $kelasFilter);

        /** @var array<int, array<string, mixed>> $cachedProfiles */
        $cachedProfiles = Cache::remember($cacheKey, now()->addHours(6), function () use ($selectedSchoolId, $kelasFilter): array {
            return Murid::query()
                ->with([
                    'kelas:id,nama',
                    'activeFaceProfiles:id,murid_id,label,face_descriptor',
                ])
                ->where('school_id', $selectedSchoolId)
                ->aktif()
                ->whereHas('activeFaceProfiles')
                ->when($kelasFilter !== null, fn (Builder $q) => $q->where('kelas_id', $kelasFilter))
                ->orderBy('nama_lengkap')
                ->get(['id', 'school_id', 'kelas_id', 'nama_lengkap', 'nisn', 'nis', 'foto_path'])
                ->map(fn (Murid $murid): array => [
                    'murid_id' => $murid->id,
                    'nama_lengkap' => $murid->nama_lengkap,
                    'nisn' => $murid->nisn ?: '-',
                    'nis' => $murid->nis ?: '-',
                    'kelas_nama' => $murid->kelas?->nama ?: 'Tanpa Kelas',
                    'foto_url' => $murid->foto_url,
                    'descriptors' => $murid->activeFaceProfiles->map(fn ($profile): array => [
                        'face_profile_id' => $profile->id,
                        'label' => $profile->label,
                        'descriptor' => $profile->face_descriptor,
                    ])->values()->all(),
                ])
                ->values()
                ->all();
        });

        $muridIds = array_column($cachedProfiles, 'murid_id');
        $todayAttendances = empty($muridIds)
            ? collect()
            : AbsensiMurid::query()
                ->where('school_id', $selectedSchoolId)
                ->padaTanggal($today)
                ->whereIn('murid_id', $muridIds)
                ->get(['id', 'murid_id', 'jam_masuk', 'jam_pulang', 'status'])
                ->keyBy('murid_id');

        $students = collect($cachedProfiles)->map(function (array $student) use ($todayAttendances): array {
            $todayAttendance = $todayAttendances->get($student['murid_id']);

            return array_merge($student, [
                'today_jam_masuk' => $todayAttendance?->jam_masuk?->format('H:i:s'),
                'today_jam_pulang' => $todayAttendance?->jam_pulang?->format('H:i:s'),
                'today_status' => $todayAttendance?->status,
            ]);
        })->values();

        return response()->json([
            'school_id' => $selectedSchoolId,
            'threshold' => 0.48,
            'total_students' => $students->count(),
            'students' => $students,
        ]);
    }

    /**
     * Catat kehadiran masuk atau pulang murid hasil pemindaian wajah.
     */
    public function store(StoreAbsenMuridRequest $request): JsonResponse
    {
        $data = $request->validated();

        /** @var Murid $murid */
        $murid = Murid::query()->with('kelas:id,nama')->findOrFail($data['murid_id']);
        $this->authorizeSchool($request, $murid->school_id);

        $today = today()->toDateString();
        $now = now();
        $mode = $data['mode'];

        $existing = AbsensiMurid::query()
            ->where('school_id', $murid->school_id)
            ->where('murid_id', $murid->id)
            ->padaTanggal($today)
            ->first();

        if ($mode === 'masuk') {
            if ($existing && $existing->jam_masuk) {
                return response()->json([
                    'already_recorded' => true,
                    'mode' => 'masuk',
                    'greeting' => "Selamat datang kembali, {$murid->nama_lengkap}.",
                    'message' => "{$murid->nama_lengkap} sudah tercatat absen masuk hari ini pukul {$existing->jam_masuk->format('H:i')}.",
                    'attendance' => $this->formatAttendancePayload($existing, $murid),
                    'stats' => $this->computeDailyStats($murid->school_id, $today),
                ]);
            }

            $status = 'hadir';
            if (! empty($data['jam_batas_masuk'])) {
                $cutoff = Carbon::createFromFormat('Y-m-d H:i', "{$today} {$data['jam_batas_masuk']}", config('app.timezone'));
                if ($cutoff && $now->greaterThan($cutoff)) {
                    $status = 'terlambat';
                }
            }

            $activeSemester = Semester::activeFor($murid->school_id);
            $activeTaId = $activeSemester?->tahun_ajaran_id ?? TahunAjaran::activeFor($murid->school_id)?->id;

            $fotoMasukPath = null;
            if (! empty($data['foto_base64'])) {
                $fotoMasukPath = ImageUploader::uploadBase64(
                    $data['foto_base64'],
                    'absensi/murid',
                    'absen_masuk',
                    $request->user()?->id
                );
            }

            $attendance = AbsensiMurid::create([
                'school_id' => $murid->school_id,
                'murid_id' => $murid->id,
                'kelas_id' => $murid->kelas_id,
                'tahun_ajaran_id' => $activeTaId,
                'semester_id' => $activeSemester?->id,
                'face_profile_id' => $data['face_profile_id'] ?? null,
                'tanggal' => $today,
                'jam_masuk' => $now,
                'status' => $status,
                'metode' => 'face_recognition',
                'match_score' => isset($data['match_score']) ? round((float) $data['match_score'], 4) : null,
                'foto_masuk_path' => $fotoMasukPath,
                'recorded_by' => $request->user()?->id,
            ]);

            return response()->json([
                'already_recorded' => false,
                'mode' => 'masuk',
                'greeting' => "Selamat datang, {$murid->nama_lengkap}!",
                'message' => "Absen masuk {$murid->nama_lengkap} berhasil dicatat pada pukul {$now->format('H:i:s')}.",
                'attendance' => $this->formatAttendancePayload($attendance, $murid),
                'stats' => $this->computeDailyStats($murid->school_id, $today),
            ], 201);
        }

        // Mode pulang
        if (! $existing || ! $existing->jam_masuk) {
            return response()->json([
                'already_recorded' => false,
                'mode' => 'pulang',
                'message' => "{$murid->nama_lengkap} belum tercatat absen masuk hari ini.",
            ], 422);
        }

        if ($existing->jam_pulang) {
            return response()->json([
                'already_recorded' => true,
                'mode' => 'pulang',
                'greeting' => "Sampai jumpa, {$murid->nama_lengkap}.",
                'message' => "{$murid->nama_lengkap} sudah tercatat absen pulang hari ini pukul {$existing->jam_pulang->format('H:i')}.",
                'attendance' => $this->formatAttendancePayload($existing, $murid),
                'stats' => $this->computeDailyStats($murid->school_id, $today),
            ]);
        }

        $fotoPulangPath = null;
        if (! empty($data['foto_base64'])) {
            $fotoPulangPath = ImageUploader::uploadBase64(
                $data['foto_base64'],
                'absensi/murid',
                'absen_pulang',
                $request->user()?->id
            );
        }

        $existing->update([
            'jam_pulang' => $now,
            'foto_pulang_path' => $fotoPulangPath ?? $existing->foto_pulang_path,
        ]);

        return response()->json([
            'already_recorded' => false,
            'mode' => 'pulang',
            'greeting' => "Sampai jumpa, {$murid->nama_lengkap}!",
            'message' => "Absen pulang {$murid->nama_lengkap} berhasil dicatat pada pukul {$now->format('H:i:s')}.",
            'attendance' => $this->formatAttendancePayload($existing->fresh(), $murid),
            'stats' => $this->computeDailyStats($murid->school_id, $today),
        ]);
    }

    /**
     * Hapus catatan absensi murid tertentu (misalnya hasil uji coba atau kesalahan deteksi).
     */
    public function destroy(Request $request, AbsensiMurid $absensi): RedirectResponse
    {
        $this->authorizeSchool($request, $absensi->school_id);

        $absensi->loadMissing('murid:id,nama_lengkap');
        $schoolId = $absensi->school_id;
        $nama = $absensi->murid?->nama_lengkap ?: 'Murid';

        ImageUploader::delete($absensi->foto_masuk_path);
        ImageUploader::delete($absensi->foto_pulang_path);
        $absensi->delete();

        return redirect()
            ->route('absensi-kbm.wajah.index', $this->schoolRedirectParams($request, $schoolId))
            ->with('success', "Catatan absensi {$nama} berhasil dihapus.");
    }

    /**
     * Hitung ringkasan statistik harian absensi untuk kartu KPI.
     *
     * @return array{total_murid_aktif: int, wajah_terdaftar: int, hadir_hari_ini: int, terlambat_hari_ini: int, belum_absen: int}
     */
    protected function computeDailyStats(int $schoolId, string $date): array
    {
        $totalMuridAktif = Murid::query()->where('school_id', $schoolId)->aktif()->count();
        $wajahTerdaftar = Murid::query()->where('school_id', $schoolId)->aktif()->whereHas('activeFaceProfiles')->count();

        $dailyBase = AbsensiMurid::query()->where('school_id', $schoolId)->padaTanggal($date);
        $recordedCount = (clone $dailyBase)->count();
        $hadirHariIni = (clone $dailyBase)->whereIn('status', ['hadir', 'terlambat'])->count();
        $terlambatHariIni = (clone $dailyBase)->where('status', 'terlambat')->count();

        return [
            'total_murid_aktif' => $totalMuridAktif,
            'wajah_terdaftar' => $wajahTerdaftar,
            'hadir_hari_ini' => $hadirHariIni,
            'terlambat_hari_ini' => $terlambatHariIni,
            'belum_absen' => max(0, $totalMuridAktif - $recordedCount),
        ];
    }

    /**
     * Bangun query tabel rekap absensi harian sesuai filter request.
     *
     * @return Builder<AbsensiMurid>
     */
    protected function buildFilteredQuery(Request $request, int $selectedSchoolId, string $selectedDate): Builder
    {
        $query = AbsensiMurid::query()
            ->with(['murid:id,nama_lengkap,nisn,nis,foto_path', 'kelas:id,nama,tingkatan_id', 'kelas.tingkatan:id,nama'])
            ->where('school_id', $selectedSchoolId)
            ->padaTanggal($selectedDate)
            ->orderByDesc('updated_at')
            ->orderByDesc('id');

        if ($request->filled('search')) {
            $search = trim((string) $request->query('search'));
            $query->whereHas('murid', function (Builder $q) use ($search): void {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                    ->orWhere('nisn', 'like', "%{$search}%")
                    ->orWhere('nis', 'like', "%{$search}%");
            });
        }

        if ($request->filled('kelas_id') && is_numeric($request->query('kelas_id'))) {
            $query->where('kelas_id', (int) $request->query('kelas_id'));
        }

        if ($request->filled('status')) {
            $status = (string) $request->query('status');
            if (in_array($status, ['hadir', 'terlambat', 'izin', 'sakit', 'alpha'], true)) {
                $query->where('status', $status);
            }
        }

        return $query;
    }

    /**
     * Format data ringkas absensi untuk respon JSON Kiosk.
     *
     * @return array<string, mixed>
     */
    protected function formatAttendancePayload(AbsensiMurid $attendance, Murid $murid): array
    {
        return [
            'id' => $attendance->id,
            'murid_id' => $murid->id,
            'nama_lengkap' => $murid->nama_lengkap,
            'nisn' => $murid->nisn ?: '-',
            'kelas_nama' => $murid->kelas?->nama ?: 'Tanpa Kelas',
            'foto_url' => $attendance->foto_masuk_url ?: $murid->foto_url,
            'tanggal' => $attendance->tanggal?->format('Y-m-d'),
            'jam_masuk' => $attendance->jam_masuk?->format('H:i:s'),
            'jam_pulang' => $attendance->jam_pulang?->format('H:i:s'),
            'status' => $attendance->status,
            'match_score_percent' => $attendance->match_score !== null ? round($attendance->match_score * 100, 1).'%' : '-',
        ];
    }
}
