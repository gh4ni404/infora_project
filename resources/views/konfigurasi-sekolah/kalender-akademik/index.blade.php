@extends('layouts.app')

@section('content')
<div class="page-header">
    <div>
        <h2 class="page-title">Kalender Akademik</h2>
        <div class="page-subtitle">
            Manajemen agenda kegiatan sekolah, jadwal ujian, rentang semester, dan hari libur
            @if ($selectedSchool)
                &ndash; <strong class="text-primary">{{ $selectedSchool->name }}</strong>
            @endif
        </div>
    </div>
    <div class="page-actions">
        {{-- Selector Unit Sekolah --}}
        @if ($schools->count() > 1)
            <form method="GET" action="{{ route('konfigurasi-sekolah.kalender-akademik') }}" id="schoolFilterForm">
                <input type="hidden" name="view" value="{{ $activeView }}" id="schoolFilterView">
                <select name="school_id" class="form-select-sm" onchange="this.form.submit()" title="Pilih Unit Sekolah">
                    @foreach ($schools as $school)
                        <option value="{{ $school->id }}" {{ $selectedSchoolId === $school->id ? 'selected' : '' }}>
                            {{ $school->name }} ({{ $school->school_type }})
                        </option>
                    @endforeach
                </select>
            </form>
        @endif

        {{-- View Switcher (Kalender vs Tabel) Menggunakan Sistem Tab Pills Global --}}
        <div class="tab-pills" role="tablist" aria-label="Pilihan Tampilan">
            <button
                type="button"
                class="tab-pill {{ $activeView === 'kalender' ? 'active' : '' }}"
                id="btnToggleCalendarView"
                title="Tampilan Kalender Bulanan"
                aria-selected="{{ $activeView === 'kalender' ? 'true' : 'false' }}"
            >
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                    <line x1="16" y1="2" x2="16" y2="6"></line>
                    <line x1="8" y1="2" x2="8" y2="6"></line>
                    <line x1="3" y1="10" x2="21" y2="10"></line>
                </svg>
                <span>Kalender</span>
            </button>
            <button
                type="button"
                class="tab-pill {{ $activeView === 'tabel' ? 'active' : '' }}"
                id="btnToggleTableView"
                title="Tampilan Tabel Agenda"
                aria-selected="{{ $activeView === 'tabel' ? 'true' : 'false' }}"
            >
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="8" y1="6" x2="21" y2="6"></line>
                    <line x1="8" y1="12" x2="21" y2="12"></line>
                    <line x1="8" y1="18" x2="21" y2="18"></line>
                    <line x1="3" y1="6" x2="3.01" y2="6"></line>
                    <line x1="3" y1="12" x2="3.01" y2="12"></line>
                    <line x1="3" y1="18" x2="3.01" y2="18"></line>
                </svg>
                <span>Tabel Agenda</span>
            </button>
        </div>

        {{-- Tombol Tambah Agenda --}}
        <button type="button" class="btn-primary" id="btnOpenCreateKalender">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="12" y1="5" x2="12" y2="19"></line>
                <line x1="5" y1="12" x2="19" y2="12"></line>
            </svg>
            <span>Tambah Agenda</span>
        </button>
    </div>
</div>

@if (session('success'))
    <div class="alert-success">
        <div class="alert-content">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                <polyline points="22 4 12 14.01 9 11.01"></polyline>
            </svg>
            <span>{{ session('success') }}</span>
        </div>
    </div>
@endif

@if ($errors->any())
    <div class="alert-danger">
        <div class="alert-content">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="12" y1="8" x2="12" y2="12"></line>
                <line x1="12" y1="16" x2="12.01" y2="16"></line>
            </svg>
            <span>{{ $errors->first() }}</span>
        </div>
    </div>
@endif

{{-- ========================================================================
     TAMPILAN 1: KALENDER BULANAN INTERAKTIF
     ======================================================================== --}}
<div id="panelCalendarView" class="{{ $activeView === 'kalender' ? '' : 'hidden' }}">
    <div class="table-card">
        <div class="table-toolbar">
            {{-- Navigasi Bulan / Tahun Universal --}}
            <div class="toolbar-nav">
                <button type="button" class="btn-icon" id="btnPrevMonth" title="Bulan Sebelumnya">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="15 18 9 12 15 6"></polyline>
                    </svg>
                </button>
                <div class="toolbar-label" id="calendarCurrentMonthLabel">
                    <!-- Diisi JavaScript -->
                </div>
                <button type="button" class="btn-icon" id="btnNextMonth" title="Bulan Selanjutnya">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="9 18 15 12 9 6"></polyline>
                    </svg>
                </button>
                <button type="button" class="btn-secondary" id="btnCalendarToday">Hari Ini</button>
            </div>

            {{-- Quick Jump Dropdown --}}
            <div class="page-actions">
                <select id="selectCalendarMonth" class="form-select-sm" title="Lompat ke Bulan">
                    <option value="1">Januari</option>
                    <option value="2">Februari</option>
                    <option value="3">Maret</option>
                    <option value="4">April</option>
                    <option value="5">Mei</option>
                    <option value="6">Juni</option>
                    <option value="7">Juli</option>
                    <option value="8">Agustus</option>
                    <option value="9">September</option>
                    <option value="10">Oktober</option>
                    <option value="11">November</option>
                    <option value="12">Desember</option>
                </select>

                <select id="selectCalendarYear" class="form-select-sm" title="Lompat ke Tahun">
                    @for ($y = (int) date('Y') - 2; $y <= (int) date('Y') + 3; $y++)
                        <option value="{{ $y }}" {{ $y === (int) date('Y') ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
            </div>
        </div>

        {{-- Baris Legenda Universal --}}
        <div class="legend-bar">
            <span class="table-cell-muted">Kategori:</span>
            <div class="legend-item">
                <span class="legend-dot dot-blue"></span>
                <span>KBM Efektif</span>
            </div>
            <div class="legend-item">
                <span class="legend-dot dot-indigo"></span>
                <span>Ujian / Asesmen</span>
            </div>
            <div class="legend-item">
                <span class="legend-dot dot-rose"></span>
                <span>Libur Nasional</span>
            </div>
            <div class="legend-item">
                <span class="legend-dot dot-amber"></span>
                <span>Libur Semester</span>
            </div>
            <div class="legend-item">
                <span class="legend-dot dot-emerald"></span>
                <span>Kegiatan Sekolah</span>
            </div>
            <div class="legend-item">
                <span class="legend-dot dot-purple"></span>
                <span>Khusus SMK</span>
            </div>
        </div>

        {{-- Header Nama Hari (Senin - Minggu) Menggunakan Grid Universal --}}
        <div class="schedule-grid">
            <div class="schedule-header-cell">Senin</div>
            <div class="schedule-header-cell">Selasa</div>
            <div class="schedule-header-cell">Rabu</div>
            <div class="schedule-header-cell">Kamis</div>
            <div class="schedule-header-cell">Jumat</div>
            <div class="schedule-header-cell is-weekend">Sabtu</div>
            <div class="schedule-header-cell is-weekend">Minggu</div>
        </div>

        {{-- Sel-sel Tanggal Grid (Render Dinamis oleh JavaScript) --}}
        <div class="schedule-grid" id="calendarGridBody">
            <!-- Diisi secara dinamis via JavaScript -->
        </div>
    </div>
</div>

{{-- ========================================================================
     TAMPILAN 2: TABEL AGENDA KOMPREHENSIF
     ======================================================================== --}}
<div id="panelTableView" class="{{ $activeView === 'tabel' ? '' : 'hidden' }}">
    <div class="table-card">
        <div class="table-toolbar">
            <form method="GET" action="{{ route('konfigurasi-sekolah.kalender-akademik') }}" class="table-toolbar-form">
                <input type="hidden" name="view" value="tabel">
                <input type="hidden" name="school_id" value="{{ $selectedSchoolId }}">

                <div class="page-actions">
                    <div class="per-page-selector">
                        <label for="table_per_page" class="table-cell-muted">Tampilkan:</label>
                        <select id="table_per_page" name="per_page" class="form-select-sm" onchange="this.form.submit()">
                            <option value="15" {{ $perPageInput === '15' ? 'selected' : '' }}>15</option>
                            <option value="30" {{ $perPageInput === '30' ? 'selected' : '' }}>30</option>
                            <option value="90" {{ $perPageInput === '90' ? 'selected' : '' }}>90</option>
                            <option value="semua" {{ in_array($perPageInput, ['semua', 'all'], true) ? 'selected' : '' }}>Semua</option>
                        </select>
                        <span class="table-cell-muted">data</span>
                    </div>

                    <select name="kategori" class="form-select-sm" onchange="this.form.submit()">
                        <option value="">Semua Kategori</option>
                        @foreach ($kategoriOptions as $key => $opt)
                            <option value="{{ $key }}" {{ request('kategori') === $key ? 'selected' : '' }}>
                                {{ $opt['label'] }}
                            </option>
                        @endforeach
                    </select>

                    <select name="semester" class="form-select-sm" onchange="this.form.submit()">
                        <option value="">Semua Semester</option>
                        <option value="ganjil" {{ request('semester') === 'ganjil' ? 'selected' : '' }}>Ganjil</option>
                        <option value="genap" {{ request('semester') === 'genap' ? 'selected' : '' }}>Genap</option>
                    </select>

                    @if ($availableYears->isNotEmpty())
                        <select name="tahun_ajaran" class="form-select-sm" onchange="this.form.submit()">
                            <option value="">Semua Tahun Ajaran</option>
                            @foreach ($availableYears as $ta)
                                <option value="{{ $ta }}" {{ request('tahun_ajaran') === $ta ? 'selected' : '' }}>{{ $ta }}</option>
                            @endforeach
                        </select>
                    @endif
                </div>

                <div class="search-box toolbar-search-box">
                    <svg class="search-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                    <input
                        type="text"
                        name="search"
                        class="search-input"
                        placeholder="Cari kegiatan, kategori, TA..."
                        value="{{ request('search') }}"
                    >
                </div>
            </form>
        </div>

        <div class="table-container table-responsive-scroll">
            <table class="data-table">
                <thead>
                    <tr>
                        <th class="table-col-w-sm text-center">No</th>
                        <th>Tanggal Kegiatan</th>
                        <th>Nama Kegiatan</th>
                        <th>Kategori</th>
                        <th>Semester / TA</th>
                        <th class="text-center">Libur KBM</th>
                        <th class="table-col-w-action text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($agendaList as $index => $item)
                        <tr>
                            <td class="text-center">{{ $agendaList->firstItem() + $index }}</td>
                            <td>
                                <strong>{{ $item->rentang_tanggal_formatted }}</strong>
                                <div class="table-cell-muted">
                                    @if ($item->tanggal_mulai && $item->tanggal_selesai && $item->tanggal_mulai->ne($item->tanggal_selesai))
                                        {{ $item->tanggal_mulai->diffInDays($item->tanggal_selesai) + 1 }} hari
                                    @else
                                        1 hari
                                    @endif
                                </div>
                            </td>
                            <td>
                                <div class="font-semibold">{{ $item->judul_kegiatan }}</div>
                                @if ($item->keterangan)
                                    <div class="table-cell-muted">{{ Str::limit($item->keterangan, 60) }}</div>
                                @endif
                            </td>
                            <td>
                                <span class="badge {{ $item->badge_class }}">
                                    {{ $item->kategori }}
                                </span>
                            </td>
                            <td>
                                <span class="table-cell-muted">Semester {{ ucfirst($item->semester) }} &bull; {{ $item->tahun_ajaran }}</span>
                            </td>
                            <td class="text-center">
                                @if ($item->libur_kbm)
                                    <span class="badge badge-rose">Ya (Libur)</span>
                                @else
                                    <span class="badge badge-success">Tidak</span>
                                @endif
                            </td>
                            <td>
                                <div class="table-actions table-actions-right">
                                    <button
                                        type="button"
                                        class="btn-edit btn-open-edit-kalender"
                                        title="Ubah Agenda"
                                        data-event="{{ json_encode([
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
                                        ]) }}"
                                        data-action="{{ route('konfigurasi-sekolah.kalender-akademik.update', $item) }}"
                                    >
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"></path>
                                        </svg>
                                    </button>
                                    <button
                                        type="button"
                                        class="btn-delete btn-open-delete-kalender"
                                        title="Hapus Agenda"
                                        data-title="{{ $item->judul_kegiatan }}"
                                        data-action="{{ route('konfigurasi-sekolah.kalender-akademik.destroy', $item) }}"
                                    >
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <polyline points="3 6 5 6 21 6"></polyline>
                                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <div class="empty-state">
                                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="empty-state-icon">
                                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                        <line x1="16" y1="2" x2="16" y2="6"></line>
                                        <line x1="8" y1="2" x2="8" y2="6"></line>
                                        <line x1="3" y1="10" x2="21" y2="10"></line>
                                    </svg>
                                    <span class="empty-state-text">Belum ada agenda kalender akademik yang tercatat. Klik "Tambah Agenda" untuk membuat kegiatan baru.</span>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="table-footer">
            {{ $agendaList->links() }}
        </div>
    </div>
</div>

{{-- Modal Konfirmasi Hapus --}}
<div class="modal-backdrop hidden" id="modalDeleteKalender" role="dialog" aria-modal="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-header">
            <div>
                <h3 class="modal-title text-danger">Hapus Agenda Kalender</h3>
                <p class="modal-subtitle">Konfirmasi penghapusan kegiatan akademik</p>
            </div>
            <button type="button" class="modal-close-btn" id="btnCloseDeleteKalender" aria-label="Tutup">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>
        <form method="POST" action="" id="formDeleteKalender">
            @csrf
            @method('DELETE')
            <input type="hidden" name="view" value="{{ $activeView }}">
            <div class="modal-body">
                <p class="text-secondary">Apakah Anda yakin ingin menghapus agenda <strong id="deleteKalenderTitle" class="text-primary"></strong> dari kalender akademik?</p>
                <div class="alert-danger alert-compact">
                    <div class="alert-content">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
                            <line x1="12" y1="9" x2="12" y2="13"></line>
                            <line x1="12" y1="17" x2="12.01" y2="17"></line>
                        </svg>
                        <span>Tindakan ini tidak dapat dibatalkan.</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" id="btnCancelDeleteKalender">Batal</button>
                <button type="submit" class="btn-danger">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="3 6 5 6 21 6"></polyline>
                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                    </svg>
                    <span>Hapus Sekarang</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Tambah Agenda Component -->
@include('konfigurasi-sekolah.kalender-akademik.create')

<!-- Modal Ubah Agenda Component -->
@include('konfigurasi-sekolah.kalender-akademik.edit')

<!-- Modal Detail Agenda per Tanggal Component -->
@include('konfigurasi-sekolah.kalender-akademik.detail')

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Dataset Event Kalender dari Backend
    const calendarEventsData = @json($calendarEvents);
    const selectedSchoolId = {{ $selectedSchoolId }};

    // State Kalender Visual
    let currentCalendarYear = {{ $currentYear }};
    let currentCalendarMonth = {{ $currentMonth }}; // 1 - 12
    let activeViewMode = '{{ $activeView }}';

    const monthNames = [
        'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];

    // Elemen DOM Tampilan
    const panelCalendar = document.getElementById('panelCalendarView');
    const panelTable = document.getElementById('panelTableView');
    const btnToggleCalendar = document.getElementById('btnToggleCalendarView');
    const btnToggleTable = document.getElementById('btnToggleTableView');
    const schoolFilterView = document.getElementById('schoolFilterView');

    // Switcher Kalender vs Tabel
    function switchView(mode) {
        activeViewMode = mode;
        if (schoolFilterView) schoolFilterView.value = mode;

        if (mode === 'kalender') {
            panelCalendar.classList.remove('hidden');
            panelTable.classList.add('hidden');
            btnToggleCalendar.classList.add('active');
            btnToggleCalendar.setAttribute('aria-selected', 'true');
            btnToggleTable.classList.remove('active');
            btnToggleTable.setAttribute('aria-selected', 'false');
        } else {
            panelCalendar.classList.add('hidden');
            panelTable.classList.remove('hidden');
            btnToggleTable.classList.add('active');
            btnToggleTable.setAttribute('aria-selected', 'true');
            btnToggleCalendar.classList.remove('active');
            btnToggleCalendar.setAttribute('aria-selected', 'false');
        }

        // Sinkronisasi hidden input view pada form modal
        const createViewInput = document.querySelector('#formCreateKalender input[name="view"]');
        const editViewInput = document.querySelector('#formEditKalender input[name="view"]');
        const deleteViewInput = document.querySelector('#formDeleteKalender input[name="view"]');
        if (createViewInput) createViewInput.value = mode;
        if (editViewInput) editViewInput.value = mode;
        if (deleteViewInput) deleteViewInput.value = mode;
    }

    btnToggleCalendar && btnToggleCalendar.addEventListener('click', () => switchView('kalender'));
    btnToggleTable && btnToggleTable.addEventListener('click', () => switchView('tabel'));

    // Elemen Kontrol Kalender
    const labelCurrentMonth = document.getElementById('calendarCurrentMonthLabel');
    const btnPrevMonth = document.getElementById('btnPrevMonth');
    const btnNextMonth = document.getElementById('btnNextMonth');
    const btnCalendarToday = document.getElementById('btnCalendarToday');
    const selectCalendarMonth = document.getElementById('selectCalendarMonth');
    const selectCalendarYear = document.getElementById('selectCalendarYear');
    const gridBody = document.getElementById('calendarGridBody');

    // Helper: format YYYY-MM-DD
    function formatDateYMD(year, month, day) {
        const m = String(month).padStart(2, '0');
        const d = String(day).padStart(2, '0');
        return `${year}-${m}-${d}`;
    }

    // Helper: cari event yang aktif pada tanggal tertentu
    function getEventsForDate(dateString) {
        return calendarEventsData.filter(event => {
            if (!event.tanggal_mulai) return false;
            const start = event.tanggal_mulai;
            const end = event.tanggal_selesai || event.tanggal_mulai;
            return dateString >= start && dateString <= end;
        });
    }

    // Render Grid Kalender
    function renderCalendarGrid() {
        if (!gridBody) return;
        gridBody.innerHTML = '';

        labelCurrentMonth.textContent = `${monthNames[currentCalendarMonth - 1]} ${currentCalendarYear}`;
        if (selectCalendarMonth) selectCalendarMonth.value = currentCalendarMonth;
        if (selectCalendarYear) selectCalendarYear.value = currentCalendarYear;

        const firstDayOfMonth = new Date(currentCalendarYear, currentCalendarMonth - 1, 1);
        const daysInCurrentMonth = new Date(currentCalendarYear, currentCalendarMonth, 0).getDate();

        // Hari dalam seminggu (0 = Minggu, 1 = Senin, ..., 6 = Sabtu)
        // Di Indonesia kalender dimulai dari Senin (index 1) sampai Minggu (index 0 / 7)
        let startingDayIndex = firstDayOfMonth.getDay();
        startingDayIndex = startingDayIndex === 0 ? 6 : startingDayIndex - 1; // 0 for Senin, 6 for Minggu

        // Hari di bulan sebelumnya
        const prevMonthDate = new Date(currentCalendarYear, currentCalendarMonth - 1, 0);
        const daysInPrevMonth = prevMonthDate.getDate();

        const todayObj = new Date();
        const todayString = formatDateYMD(todayObj.getFullYear(), todayObj.getMonth() + 1, todayObj.getDate());

        // 1. Render sel dari bulan sebelumnya
        for (let i = startingDayIndex - 1; i >= 0; i--) {
            const dayNum = daysInPrevMonth - i;
            const prevMonth = currentCalendarMonth === 1 ? 12 : currentCalendarMonth - 1;
            const prevYear = currentCalendarMonth === 1 ? currentCalendarYear - 1 : currentCalendarYear;
            const dateStr = formatDateYMD(prevYear, prevMonth, dayNum);

            createDayCell(dayNum, dateStr, true);
        }

        // 2. Render sel bulan saat ini
        for (let dayNum = 1; dayNum <= daysInCurrentMonth; dayNum++) {
            const dateStr = formatDateYMD(currentCalendarYear, currentCalendarMonth, dayNum);
            const isToday = dateStr === todayString;

            createDayCell(dayNum, dateStr, false, isToday);
        }

        // 3. Render sel bulan berikutnya agar genap kelipatan 7
        const totalCellsSoFar = startingDayIndex + daysInCurrentMonth;
        const remainingCells = (7 - (totalCellsSoFar % 7)) % 7;
        for (let dayNum = 1; dayNum <= remainingCells; dayNum++) {
            const nextMonth = currentCalendarMonth === 12 ? 1 : currentCalendarMonth + 1;
            const nextYear = currentCalendarMonth === 12 ? currentCalendarYear + 1 : currentCalendarYear;
            const dateStr = formatDateYMD(nextYear, nextMonth, dayNum);

            createDayCell(dayNum, dateStr, true);
        }
    }

    function createDayCell(dayNum, dateStr, isOtherMonth, isToday = false) {
        const eventsOnDate = getEventsForDate(dateStr);
        const hasHoliday = eventsOnDate.some(e => e.libur_kbm);

        const dateParts = dateStr.split('-');
        const cellDate = new Date(parseInt(dateParts[0], 10), parseInt(dateParts[1], 10) - 1, parseInt(dateParts[2], 10));
        const dayOfWeek = cellDate.getDay(); // 0 = Minggu, 6 = Sabtu
        const isWeekend = (dayOfWeek === 0 || dayOfWeek === 6);

        const cell = document.createElement('div');
        cell.className = 'schedule-day-cell';
        if (isWeekend) cell.classList.add('is-weekend');
        if (isOtherMonth) cell.classList.add('is-other-month');
        if (isToday) cell.classList.add('is-today');
        if (hasHoliday) cell.classList.add('is-holiday');
        cell.dataset.date = dateStr;

        // Header sel tanggal
        const header = document.createElement('div');
        header.className = 'schedule-day-header';

        const numSpan = document.createElement('span');
        numSpan.className = 'schedule-day-num';
        if (isWeekend) numSpan.classList.add('is-weekend');
        numSpan.textContent = dayNum;
        header.appendChild(numSpan);

        if (hasHoliday) {
            const badge = document.createElement('span');
            badge.className = 'schedule-day-badge';
            badge.textContent = 'Libur';
            header.appendChild(badge);
        }

        cell.appendChild(header);

        // Daftar event badge
        const eventsContainer = document.createElement('div');
        eventsContainer.className = 'schedule-items';

        const maxVisible = 2;
        eventsOnDate.slice(0, maxVisible).forEach(event => {
            const pill = document.createElement('div');
            pill.className = `schedule-pill pill-${event.warna || 'blue'}`;
            pill.textContent = event.judul_kegiatan;
            pill.title = `${event.judul_kegiatan} (${event.kategori})`;

            // Klik pada pill langsung membuka modal edit
            pill.addEventListener('click', function(e) {
                e.stopPropagation();
                openEditModalWithData(event);
            });

            eventsContainer.appendChild(pill);
        });

        if (eventsOnDate.length > maxVisible) {
            const moreCount = document.createElement('div');
            moreCount.className = 'schedule-pill-more';
            moreCount.textContent = `+${eventsOnDate.length - maxVisible} lainnya`;
            eventsContainer.appendChild(moreCount);
        }

        cell.appendChild(eventsContainer);

        // Klik sel tanggal memicu modal rincian tanggal
        cell.addEventListener('click', function() {
            openDateDetailModal(dateStr, eventsOnDate);
        });

        gridBody.appendChild(cell);
    }

    // Navigasi Bulan Kalender
    btnPrevMonth && btnPrevMonth.addEventListener('click', function() {
        if (currentCalendarMonth === 1) {
            currentCalendarMonth = 12;
            currentCalendarYear--;
        } else {
            currentCalendarMonth--;
        }
        renderCalendarGrid();
    });

    btnNextMonth && btnNextMonth.addEventListener('click', function() {
        if (currentCalendarMonth === 12) {
            currentCalendarMonth = 1;
            currentCalendarYear++;
        } else {
            currentCalendarMonth++;
        }
        renderCalendarGrid();
    });

    btnCalendarToday && btnCalendarToday.addEventListener('click', function() {
        const now = new Date();
        currentCalendarYear = now.getFullYear();
        currentCalendarMonth = now.getMonth() + 1;
        renderCalendarGrid();
    });

    selectCalendarMonth && selectCalendarMonth.addEventListener('change', function() {
        currentCalendarMonth = parseInt(this.value, 10);
        renderCalendarGrid();
    });

    selectCalendarYear && selectCalendarYear.addEventListener('change', function() {
        currentCalendarYear = parseInt(this.value, 10);
        renderCalendarGrid();
    });

    // Inisialisasi awal Kalender
    renderCalendarGrid();

    // ========================================================================
    // MODAL TAMBAH AGENDA
    // ========================================================================
    const modalCreate = document.getElementById('modalCreateKalender');
    const btnOpenCreate = document.getElementById('btnOpenCreateKalender');
    const btnCloseCreate = document.getElementById('btnCloseCreateKalender');
    const btnCancelCreate = document.getElementById('btnCancelCreateKalender');
    const formCreate = document.getElementById('formCreateKalender');

    function openCreateModal(prefillDate = null) {
        if (!modalCreate) return;
        if (prefillDate) {
            const startInput = document.getElementById('create_tanggal_mulai');
            const endInput = document.getElementById('create_tanggal_selesai');
            if (startInput) startInput.value = prefillDate;
            if (endInput) endInput.value = prefillDate;
        }
        modalCreate.classList.remove('hidden');
        document.body.classList.add('modal-open');
        const firstInput = modalCreate.querySelector('input[name="judul_kegiatan"]');
        if (firstInput) setTimeout(() => firstInput.focus(), 50);
    }

    function closeCreateModal() {
        if (!modalCreate) return;
        modalCreate.classList.add('hidden');
        document.body.classList.remove('modal-open');
    }

    btnOpenCreate && btnOpenCreate.addEventListener('click', () => openCreateModal());
    btnCloseCreate && btnCloseCreate.addEventListener('click', closeCreateModal);
    btnCancelCreate && btnCancelCreate.addEventListener('click', closeCreateModal);

    // Kategori change -> Auto color select
    const createKategori = document.getElementById('create_kategori');
    const createWarna = document.getElementById('create_warna');
    createKategori && createKategori.addEventListener('change', function() {
        const selectedOpt = this.options[this.selectedIndex];
        const defaultColor = selectedOpt?.dataset?.defaultColor;
        if (defaultColor && createWarna) {
            createWarna.value = defaultColor;
        }
    });

    // ========================================================================
    // MODAL RINCIAN TANGGAL
    // ========================================================================
    const modalDateDetail = document.getElementById('modalDateDetail');
    const btnCloseDateDetail = document.getElementById('btnCloseDateDetail');
    const btnDismissDateDetail = document.getElementById('btnDismissDateDetail');
    const btnAddEventFromDateDetail = document.getElementById('btnAddEventFromDateDetail');
    const titleDateDetail = document.getElementById('modalDateDetailTitle');
    const subtitleDateDetail = document.getElementById('modalDateDetailSubtitle');
    const listDateDetail = document.getElementById('dateDetailEventsList');
    let activeSelectedDate = null;

    function openDateDetailModal(dateStr, events) {
        if (!modalDateDetail) return;
        activeSelectedDate = dateStr;

        const dateParts = dateStr.split('-');
        const y = parseInt(dateParts[0], 10);
        const m = parseInt(dateParts[1], 10);
        const d = parseInt(dateParts[2], 10);
        const formattedDateText = `${d} ${monthNames[m - 1]} ${y}`;

        titleDateDetail.textContent = `Agenda: ${formattedDateText}`;
        subtitleDateDetail.textContent = events.length > 0
            ? `Terdapat ${events.length} kegiatan yang tercatat pada hari ini`
            : `Tidak ada agenda kegiatan akademik yang terjadwal pada hari ini`;

        listDateDetail.innerHTML = '';
        if (events.length === 0) {
            listDateDetail.innerHTML = `
                <div class="empty-state">
                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="empty-state-icon">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="12" y1="8" x2="12" y2="12"></line>
                        <line x1="12" y1="16" x2="12.01" y2="16"></line>
                    </svg>
                    <span class="empty-state-text">Tidak ada kegiatan akademik pada tanggal ini. Klik tombol di bawah untuk menambahkan agenda baru.</span>
                </div>
            `;
        } else {
            events.forEach(item => {
                const itemDiv = document.createElement('div');
                itemDiv.className = 'card-list-item';
                itemDiv.innerHTML = `
                    <div class="card-list-item-header">
                        <span class="card-list-item-title">${item.judul_kegiatan}</span>
                        <span class="badge ${item.badge_class || 'badge-blue'}">${item.kategori}</span>
                    </div>
                    <div class="card-list-item-meta">
                        <span><strong>Rentang:</strong> ${item.rentang_formatted || item.tanggal_mulai}</span>
                        <span>&bull;</span>
                        <span><strong>Semester:</strong> ${item.semester ? item.semester.toUpperCase() : '-'} (${item.tahun_ajaran})</span>
                        ${item.libur_kbm ? '<span class="badge badge-rose">Libur KBM</span>' : ''}
                    </div>
                    ${item.keterangan ? `<div class="card-list-item-desc">${item.keterangan}</div>` : ''}
                    <div class="table-actions table-actions-right card-list-item-actions">
                        <button type="button" class="btn-secondary btn-sm btn-edit-from-detail" title="Ubah Agenda Ini">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"></path>
                            </svg>
                            <span>Ubah Agenda</span>
                        </button>
                    </div>
                `;

                const editBtn = itemDiv.querySelector('.btn-edit-from-detail');
                editBtn && editBtn.addEventListener('click', function() {
                    closeDateDetailModal();
                    openEditModalWithData(item);
                });

                listDateDetail.appendChild(itemDiv);
            });
        }

        modalDateDetail.classList.remove('hidden');
        document.body.classList.add('modal-open');
    }

    function closeDateDetailModal() {
        if (!modalDateDetail) return;
        modalDateDetail.classList.add('hidden');
        document.body.classList.remove('modal-open');
    }

    btnCloseDateDetail && btnCloseDateDetail.addEventListener('click', closeDateDetailModal);
    btnDismissDateDetail && btnDismissDateDetail.addEventListener('click', closeDateDetailModal);

    btnAddEventFromDateDetail && btnAddEventFromDateDetail.addEventListener('click', function() {
        const dateToPrefill = activeSelectedDate;
        closeDateDetailModal();
        openCreateModal(dateToPrefill);
    });

    // ========================================================================
    // MODAL UBAH AGENDA
    // ========================================================================
    const modalEdit = document.getElementById('modalEditKalender');
    const btnCloseEdit = document.getElementById('btnCloseEditKalender');
    const btnCancelEdit = document.getElementById('btnCancelEditKalender');
    const formEdit = document.getElementById('formEditKalender');

    function openEditModalWithData(eventData) {
        if (!modalEdit || !formEdit) return;

        formEdit.action = `{{ url('konfigurasi-sekolah/kalender-akademik') }}/${eventData.id}`;
        document.getElementById('edit_kalender_id').value = eventData.id || '';

        const schoolSelect = document.getElementById('edit_school_id');
        if (schoolSelect) schoolSelect.value = eventData.school_id || '';

        document.getElementById('edit_tahun_ajaran').value = eventData.tahun_ajaran || '';
        document.getElementById('edit_semester').value = eventData.semester || 'ganjil';
        document.getElementById('edit_judul_kegiatan').value = eventData.judul_kegiatan || '';
        document.getElementById('edit_tanggal_mulai').value = eventData.tanggal_mulai || '';
        document.getElementById('edit_tanggal_selesai').value = eventData.tanggal_selesai || eventData.tanggal_mulai || '';
        document.getElementById('edit_kategori').value = eventData.kategori || 'Kegiatan Sekolah';
        document.getElementById('edit_warna').value = eventData.warna || 'blue';
        document.getElementById('edit_libur_kbm').checked = Boolean(eventData.libur_kbm);
        document.getElementById('edit_keterangan').value = eventData.keterangan || '';

        modalEdit.classList.remove('hidden');
        document.body.classList.add('modal-open');
        const firstInput = modalEdit.querySelector('input[name="judul_kegiatan"]');
        if (firstInput) setTimeout(() => firstInput.focus(), 50);
    }

    function closeEditModal() {
        if (!modalEdit) return;
        modalEdit.classList.add('hidden');
        document.body.classList.remove('modal-open');
    }

    btnCloseEdit && btnCloseEdit.addEventListener('click', closeEditModal);
    btnCancelEdit && btnCancelEdit.addEventListener('click', closeEditModal);

    // Kategori change in edit modal -> Auto color
    const editKategori = document.getElementById('edit_kategori');
    const editWarna = document.getElementById('edit_warna');
    editKategori && editKategori.addEventListener('change', function() {
        const selectedOpt = this.options[this.selectedIndex];
        const defaultColor = selectedOpt?.dataset?.defaultColor;
        if (defaultColor && editWarna) {
            editWarna.value = defaultColor;
        }
    });

    // Listener tombol edit di tabel
    document.querySelectorAll('.btn-open-edit-kalender').forEach(btn => {
        btn.addEventListener('click', function() {
            try {
                const eventData = JSON.parse(this.dataset.event);
                openEditModalWithData(eventData);
            } catch (err) {
                console.error('Gagal membaca data event untuk modal edit:', err);
            }
        });
    });

    // ========================================================================
    // MODAL HAPUS AGENDA
    // ========================================================================
    const modalDelete = document.getElementById('modalDeleteKalender');
    const btnCloseDelete = document.getElementById('btnCloseDeleteKalender');
    const btnCancelDelete = document.getElementById('btnCancelDeleteKalender');
    const formDelete = document.getElementById('formDeleteKalender');
    const deleteTitle = document.getElementById('deleteKalenderTitle');

    function openDeleteModal(actionUrl, title) {
        if (!modalDelete || !formDelete) return;
        formDelete.action = actionUrl;
        if (deleteTitle) deleteTitle.textContent = title || 'agenda ini';
        modalDelete.classList.remove('hidden');
        document.body.classList.add('modal-open');
    }

    function closeDeleteModal() {
        if (!modalDelete) return;
        modalDelete.classList.add('hidden');
        document.body.classList.remove('modal-open');
    }

    btnCloseDelete && btnCloseDelete.addEventListener('click', closeDeleteModal);
    btnCancelDelete && btnCancelDelete.addEventListener('click', closeDeleteModal);

    document.querySelectorAll('.btn-open-delete-kalender').forEach(btn => {
        btn.addEventListener('click', function() {
            const actionUrl = this.dataset.action;
            const title = this.dataset.title;
            openDeleteModal(actionUrl, title);
        });
    });

    // Tutup modal via tombol Escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeCreateModal();
            closeEditModal();
            closeDateDetailModal();
            closeDeleteModal();
        }
    });

    // Auto reopen modal jika validasi server gagal
    @if (old('_method') === 'PUT')
        @if (old('kalender_id'))
            const prevData = {
                id: '{{ old('kalender_id') }}',
                school_id: '{{ old('school_id') }}',
                tahun_ajaran: '{{ old('tahun_ajaran') }}',
                semester: '{{ old('semester') }}',
                judul_kegiatan: '{{ old('judul_kegiatan') }}',
                tanggal_mulai: '{{ old('tanggal_mulai') }}',
                tanggal_selesai: '{{ old('tanggal_selesai') }}',
                kategori: '{{ old('kategori') }}',
                warna: '{{ old('warna') }}',
                libur_kbm: {{ old('libur_kbm') ? 'true' : 'false' }},
                keterangan: '{{ old('keterangan') }}',
            };
            openEditModalWithData(prevData);
        @endif
    @elseif ($errors->any())
        openCreateModal();
    @endif
});
</script>
@endsection

