<?php

namespace App\Http\Controllers\KonfigurasiSekolah;

use App\Http\Controllers\Controller;
use App\Http\Requests\KonfigurasiSekolah\StoreKalenderAkademikRequest;
use App\Http\Requests\KonfigurasiSekolah\UpdateKalenderAkademikRequest;
use App\Models\KalenderAkademik;
use App\Models\School;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KalenderAkademikController extends Controller
{
    /**
     * Daftar preset kategori standar akademik.
     */
    public const KATEGORI_OPTIONS = [
        'KBM Efektif' => ['color' => 'blue', 'label' => 'KBM Efektif', 'description' => 'Kegiatan Belajar Mengajar Reguler'],
        'Ujian/Asesmen' => ['color' => 'indigo', 'label' => 'Ujian / Asesmen', 'description' => 'PTS, PAS, PAT, ANBK, Ujian Sekolah'],
        'Libur Nasional' => ['color' => 'rose', 'label' => 'Libur Nasional', 'description' => 'Hari Libur Nasional & Cuti Bersama'],
        'Libur Semester' => ['color' => 'amber', 'label' => 'Libur Semester', 'description' => 'Libur Akhir Semester Ganjil / Genap'],
        'Kegiatan Sekolah' => ['color' => 'emerald', 'label' => 'Kegiatan Sekolah', 'description' => 'MPLS, Peringatan Hari Besar, Rapat Guru'],
        'Khusus SMK' => ['color' => 'purple', 'label' => 'Khusus SMK', 'description' => 'UKK, Pelepasan & Penarikan PKL Magang'],
    ];

    /**
     * Daftar pilihan warna penanda kegiatan.
     */
    public const WARNA_OPTIONS = [
        'blue' => 'Biru (Royal)',
        'emerald' => 'Hijau (Emerald)',
        'amber' => 'Kuning (Amber)',
        'rose' => 'Merah (Rose)',
        'purple' => 'Ungu (Purple)',
        'indigo' => 'Indigo (Navy)',
    ];

    /**
     * Menampilkan antarmuka kalender akademik dan daftar agenda.
     */
    public function index(Request $request): View
    {
        // Ambil daftar sekolah aktif untuk dropdown selector
        $schools = School::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'npsn', 'school_type']);

        // Tentukan sekolah terpilih (default: sekolah pertama)
        $selectedSchoolId = (int) $request->query('school_id', $schools->first()?->id ?? 0);
        $selectedSchool = $schools->firstWhere('id', $selectedSchoolId) ?? $schools->first();
        if ($selectedSchool && $selectedSchoolId !== $selectedSchool->id) {
            $selectedSchoolId = $selectedSchool->id;
        }

        // Filter bulan & tahun untuk kalender visual
        $currentMonth = (int) $request->query('bulan', Carbon::now()->month);
        $currentYear = (int) $request->query('tahun', Carbon::now()->year);

        // Ambil semua event untuk sekolah terpilih (untuk inisialisasi kalender interaktif)
        $calendarEvents = collect();
        if ($selectedSchoolId > 0) {
            $calendarEvents = KalenderAkademik::query()
                ->where('school_id', $selectedSchoolId)
                ->orderBy('tanggal_mulai')
                ->get()
                ->map(fn (KalenderAkademik $item) => [
                    'id' => $item->id,
                    'school_id' => $item->school_id,
                    'judul_kegiatan' => $item->judul_kegiatan,
                    'tanggal_mulai' => $item->tanggal_mulai?->format('Y-m-d'),
                    'tanggal_selesai' => $item->tanggal_selesai?->format('Y-m-d'),
                    'tahun_ajaran' => $item->tahun_ajaran,
                    'semester' => $item->semester,
                    'kategori' => $item->kategori,
                    'warna' => $item->warna,
                    'libur_kbm' => $item->libur_kbm,
                    'keterangan' => $item->keterangan,
                    'rentang_formatted' => $item->rentang_tanggal_formatted,
                    'badge_class' => $item->badge_class,
                ]);
        }

        // Query tabel agenda dengan filter
        $tableQuery = KalenderAkademik::query()->with('school');

        if ($selectedSchoolId > 0) {
            $tableQuery->where('school_id', $selectedSchoolId);
        }

        if ($request->filled('search')) {
            $search = trim((string) $request->query('search'));
            $tableQuery->where(function ($q) use ($search) {
                $q->where('judul_kegiatan', 'like', "%{$search}%")
                    ->orWhere('kategori', 'like', "%{$search}%")
                    ->orWhere('tahun_ajaran', 'like', "%{$search}%")
                    ->orWhere('keterangan', 'like', "%{$search}%");
            });
        }

        if ($request->filled('kategori')) {
            $tableQuery->where('kategori', $request->query('kategori'));
        }

        if ($request->filled('semester')) {
            $tableQuery->where('semester', $request->query('semester'));
        }

        if ($request->filled('tahun_ajaran')) {
            $tableQuery->where('tahun_ajaran', $request->query('tahun_ajaran'));
        }

        // Opsi pagination
        $perPageInput = $request->query('per_page', '15');
        if ($perPageInput === 'semua' || $perPageInput === 'all') {
            $perPage = 1000;
        } elseif (in_array($perPageInput, ['15', '30', '90'], true)) {
            $perPage = (int) $perPageInput;
        } else {
            $perPage = 15;
            $perPageInput = '15';
        }

        $agendaList = $tableQuery
            ->orderBy('tanggal_mulai', 'desc')
            ->orderBy('id', 'desc')
            ->paginate($perPage)
            ->withQueryString();

        // Opsi filter tahun ajaran unik yang sudah tersimpan
        $availableYears = KalenderAkademik::query()
            ->when($selectedSchoolId > 0, fn ($q) => $q->where('school_id', $selectedSchoolId))
            ->distinct()
            ->pluck('tahun_ajaran')
            ->filter()
            ->values();

        // Default tahun ajaran jika belum ada (misal 2026/2027)
        $defaultTahunAjaran = $availableYears->first() ?? '2026/2027';

        // Tampilan aktif (kalender atau tabel)
        $activeView = $request->query('view', 'kalender');
        if (! in_array($activeView, ['kalender', 'tabel'], true)) {
            $activeView = 'kalender';
        }

        return view('konfigurasi-sekolah.kalender-akademik.index', [
            'schools' => $schools,
            'selectedSchool' => $selectedSchool,
            'selectedSchoolId' => $selectedSchoolId,
            'calendarEvents' => $calendarEvents,
            'agendaList' => $agendaList,
            'currentMonth' => $currentMonth,
            'currentYear' => $currentYear,
            'availableYears' => $availableYears,
            'defaultTahunAjaran' => $defaultTahunAjaran,
            'activeView' => $activeView,
            'perPageInput' => $perPageInput,
            'kategoriOptions' => self::KATEGORI_OPTIONS,
            'warnaOptions' => self::WARNA_OPTIONS,
        ]);
    }

    /**
     * Endpoint API murni (JSON) untuk mengambil event kalender berdasarkan sekolah dan bulan.
     */
    public function events(Request $request): JsonResponse
    {
        $schoolId = (int) $request->query('school_id');
        $tahun = (int) $request->query('tahun', Carbon::now()->year);
        $bulan = (int) $request->query('bulan', Carbon::now()->month);

        $query = KalenderAkademik::query();

        if ($schoolId > 0) {
            $query->where('school_id', $schoolId);
        }

        if ($request->filled('tahun') && $request->filled('bulan')) {
            $query->bulan($tahun, $bulan);
        }

        $events = $query->orderBy('tanggal_mulai')->get()->map(fn (KalenderAkademik $item) => [
            'id' => $item->id,
            'school_id' => $item->school_id,
            'judul_kegiatan' => $item->judul_kegiatan,
            'tanggal_mulai' => $item->tanggal_mulai?->format('Y-m-d'),
            'tanggal_selesai' => $item->tanggal_selesai?->format('Y-m-d'),
            'tahun_ajaran' => $item->tahun_ajaran,
            'semester' => $item->semester,
            'kategori' => $item->kategori,
            'warna' => $item->warna,
            'libur_kbm' => $item->libur_kbm,
            'keterangan' => $item->keterangan,
            'rentang_formatted' => $item->rentang_tanggal_formatted,
            'badge_class' => $item->badge_class,
        ]);

        return response()->json([
            'success' => true,
            'data' => $events,
        ]);
    }

    /**
     * Menyimpan data kegiatan kalender akademik baru.
     */
    public function store(StoreKalenderAkademikRequest $request): RedirectResponse|JsonResponse
    {
        $data = $request->validated();
        $event = KalenderAkademik::create($data);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Kegiatan kalender akademik berhasil ditambahkan.',
                'data' => $event,
            ], 201);
        }

        return redirect()
            ->route('konfigurasi-sekolah.kalender-akademik', [
                'school_id' => $event->school_id,
                'view' => $request->input('view', 'kalender'),
            ])
            ->with('success', 'Kegiatan kalender akademik berhasil ditambahkan.');
    }

    /**
     * Form edit dialihkan ke halaman indeks dengan modal edit.
     */
    public function edit(KalenderAkademik $kalenderAkademik): RedirectResponse
    {
        return redirect()->route('konfigurasi-sekolah.kalender-akademik', [
            'school_id' => $kalenderAkademik->school_id,
        ]);
    }

    /**
     * Memperbarui data kegiatan kalender akademik.
     */
    public function update(UpdateKalenderAkademikRequest $request, KalenderAkademik $kalenderAkademik): RedirectResponse|JsonResponse
    {
        $data = $request->validated();
        $kalenderAkademik->update($data);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Kegiatan kalender akademik berhasil diperbarui.',
                'data' => $kalenderAkademik,
            ]);
        }

        return redirect()
            ->route('konfigurasi-sekolah.kalender-akademik', [
                'school_id' => $kalenderAkademik->school_id,
                'view' => $request->input('view', 'kalender'),
            ])
            ->with('success', 'Kegiatan kalender akademik berhasil diperbarui.');
    }

    /**
     * Menghapus data kegiatan kalender akademik.
     */
    public function destroy(Request $request, KalenderAkademik $kalenderAkademik): RedirectResponse|JsonResponse
    {
        $schoolId = $kalenderAkademik->school_id;
        $kalenderAkademik->delete();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Kegiatan kalender akademik berhasil dihapus.',
            ]);
        }

        return redirect()
            ->route('konfigurasi-sekolah.kalender-akademik', [
                'school_id' => $schoolId,
                'view' => $request->input('view', 'kalender'),
            ])
            ->with('success', 'Kegiatan kalender akademik berhasil dihapus.');
    }
}
