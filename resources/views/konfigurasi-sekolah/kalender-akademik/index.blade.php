@extends('layouts.app')

@section('content')
    <x-page-header title="Kalender Akademik">
        <x-slot:subtitle>
            Manajemen agenda kegiatan sekolah, jadwal ujian, rentang semester, dan hari libur
            @if ($selectedSchool)
                &ndash; <strong class="text-brand">{{ $selectedSchool->name }}</strong>
            @else
                &ndash; <span class="text-muted">Belum Terhubung Unit Sekolah</span>
            @endif
        </x-slot:subtitle>

        <x-slot:actions>
            {{-- Selector Unit Sekolah --}}
            @if ($user->isSuperAdmin() && $schools->count() > 1)
                <form method="GET" action="{{ route('konfigurasi-sekolah.kalender-akademik') }}" id="schoolFilterForm">
                    <input type="hidden" name="view" value="{{ $activeView }}" id="schoolFilterView">
                    <x-form-select
                        name="school_id"
                        id="school_id"
                        :selected="$selectedSchoolId"
                        :options="$schools->mapWithKeys(fn($school) => [$school->id => $school->name . ' (' . $school->school_type . ')'])"
                        onchange="this.form.submit()"
                        title="Pilih Unit Sekolah"
                    />
                </form>
            @endif

            {{-- View Switcher (Kalender vs Tabel) Menggunakan Sistem Tab Pills Global --}}
            <x-tab-pills label="Pilihan Tampilan">
                <x-tab-pill id="btnToggleCalendarView" :active="$activeView === 'kalender'" icon="calendar"
                    title="Tampilan Kalender Bulanan">
                    Kalender
                </x-tab-pill>
                <x-tab-pill id="btnToggleTableView" :active="$activeView === 'tabel'" icon="list-bullet"
                    title="Tampilan Tabel Agenda">
                    Tabel Agenda
                </x-tab-pill>
            </x-tab-pills>

            {{-- Tombol Tambah Agenda --}}
            <x-crud-button
                action="create"
                id="btnOpenCreateKalender"
                :disabled="!($selectedSchool && $hasTahunAjaran)"
                title="{{ !$hasTahunAjaran ? 'Unit sekolah belum memiliki konfigurasi Tahun Ajaran' : 'Tambah Agenda' }}"
            />
        </x-slot:actions>
    </x-page-header>
    {{-- Flash Alerts --}}
    <x-alert type="success" />
    <x-alert type="error" />
    <x-alert type="validation" />

    {{-- Status Banner Jika Sekolah Belum Terhubung --}}
    @if (! $selectedSchool)
        <x-status-banner
            title="Unit Sekolah Belum Dikonfigurasi:"
            message="Akun Anda belum terhubung dengan unit sekolah manapun. Silakan hubungi Super Administrator untuk konfigurasi unit sekolah."
            badge="Perlu Konfigurasi"
        />
    @elseif (! $hasTahunAjaran)
        <x-status-banner
            title="Tahun Ajaran Belum Dikonfigurasi:"
            message="<br>Unit sekolah <strong>{{ $selectedSchool->name }}</strong>  belum memiliki Tahun Ajaran.
            Kalender akademik membutuhkan setidaknya satu Tahun Ajaran untuk dapat menambahkan agenda kegiatan sekolah."
            badge="Tahun Ajaran Diperlukan"
            action-icon="settings"
            action-title="Konfigurasi Sekarang"
            :action-href="route('konfigurasi-sekolah.tahun-ajaran', array_filter(['school_id' => $user->isSuperAdmin() ? $selectedSchoolId : null]))"
        />
    @endif

    {{-- ========================================================================
     TAMPILAN 1: KALENDER BULANAN INTERAKTIF
     ======================================================================== --}}
    <div id="panelCalendarView" class="panel-calendar-view {{ $activeView === 'kalender' ? '' : 'hidden' }}">
        <div class="table-card calendar-card">
            <div class="table-toolbar">
                {{-- Navigasi Bulan / Tahun Universal --}}
                <div class="toolbar-nav">
                    <button type="button" class="btn-icon" id="btnPrevMonth" title="Bulan Sebelumnya">
                        <x-icon name="chevron-left" size="16" />
                    </button>
                    <div class="toolbar-label" id="calendarCurrentMonthLabel">
                        <!-- Diisi JavaScript -->
                    </div>
                    <button type="button" class="btn-icon" id="btnNextMonth" title="Bulan Selanjutnya">
                        <x-icon name="chevron-right" size="16" />
                    </button>
                    <button type="button" class="btn-secondary" id="btnCalendarToday">Hari Ini</button>
                </div>

                {{-- Quick Jump Dropdown --}}
                <div class="page-actions">
                    <x-form-select
                        id="selectCalendarMonth"
                        name="calendar_month"
                        class="form-select-sm"
                        title="Lompat ke Bulan"
                        :selected="(int) date('n')"
                        :options="[
                            1 => 'Januari',
                            2 => 'Februari',
                            3 => 'Maret',
                            4 => 'April',
                            5 => 'Mei',
                            6 => 'Juni',
                            7 => 'Juli',
                            8 => 'Agustus',
                            9 => 'September',
                            10 => 'Oktober',
                            11 => 'November',
                            12 => 'Desember',
                        ]"
                    />

                    <x-form-select
                        id="selectCalendarYear"
                        name="calendar_year"
                        class="form-select-sm"
                        title="Lompat ke Tahun"
                        :selected="(int) date('Y')"
                        :options="array_combine(
                            range((int) date('Y') - 2, (int) date('Y') + 3),
                            range((int) date('Y') - 2, (int) date('Y') + 3),
                        )"
                    />
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
        <x-data-table
            route="{{ route('konfigurasi-sekolah.kalender-akademik') }}"
            search-placeholder="Cari kegiatan, kategori, TA..."
            :per-page="$perPageInput">

            <x-slot:filters>
                <input type="hidden" name="view" value="tabel">
                @if ($user->isSuperAdmin())
                    <input type="hidden" name="school_id" value="{{ $selectedSchoolId }}">
                @endif

                <x-form-select
                    id="selectKategori"
                    name="kategori"
                    class="form-select-sm"
                    placeholder="Semua Kategori"
                    :selected="request('kategori')"
                    :options="$kategoriOptions"
                    onchange="this.form.submit()"
                />

                <x-form-select 
                    id="semester"
                    name="semester"
                    class="form-select-sm"
                    placeholder="Semua Semester"
                    :selected="request('semester')"
                    :options="[
                        'ganjil' => 'Ganjil',
                        'genap' => 'Genap',
                    ]"
                    onchange="this.form.submit()"
                />

                @if ($availableYears->isNotEmpty())
                    <x-form-select 
                        id="tahun_ajaran"
                        name="tahun_ajaran"
                        class="form-select-sm"
                        placeholder="Semua Tahun Ajaran"
                        :selected="request('tahun_ajaran')"
                        :options="$availableYears->combine($availableYears)"
                        onchange="this.form.submit()"
                    />
                @endif
            </x-slot:filters>

            <thead>
                <tr>
                    <th>No</th>
                    <th>Tanggal Kegiatan</th>
                    <th>Nama Kegiatan</th>
                    <th>Kategori</th>
                    <th>Semester / TA</th>
                    <th class="text-center">Libur KBM</th>
                    <th class="col-w-actions">Aksi</th>
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
                            <span class="table-cell-muted">Semester {{ ucfirst($item->semester) }} &bull;
                                {{ $item->tahun_ajaran }}</span>
                        </td>
                        <td class="text-center">
                            @if ($item->libur_kbm)
                                <span class="badge badge-rose">Ya (Libur)</span>
                            @else
                                <span class="badge badge-success">Tidak</span>
                            @endif
                        </td>
                        <td>
                            <x-table-actions align="right">
                                <x-crud-button
                                action="edit"
                                class="btn-open-edit-kalender"
                                title="Ubah Agend"
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
                                data-action="{{ route('konfigurasi-sekolah.kalender-akademik.update', $item) }}"/>
                                
                                <x-crud-button 
                                    action="delete"
                                    class="btn-open-delete-kalender"
                                    title="Hapus Agenda"
                                    data-title="{{ $item->judul_kegiatan }}"
                                    data-kategori="{{ $item->kategori }}"
                                    data-action="{{ route('konfigurasi-sekolah.kalender-akademik.destroy', $item) }}"
                                />
                            </x-table-actions>
                        </td>
                    </tr>
                @empty
                    @php
                        $isFiltered = request()->filled('search') || request()->filled('kategori') || request()->filled('semester') || request()->filled('tahun_ajaran');
                        $emptyText = $isFiltered
                            ? 'Tidak ada agenda kegiatan akademik yang cocok dengan kriteria filter pencarian Anda.'
                            : 'Belum ada agenda kalender akademik yang tercatat. Klik "Tambah Agenda" di atas untuk menambahkan agenda baru.';
                    @endphp

                    <x-empty-state
                        icon="calendar"
                        iconSize="48"
                        colspan="7"
                        :text="$emptyText"
                    />
                @endforelse
            </tbody>

            <x-slot:footer>
                <x-pagination-summary :paginator="$agendaList" />
            </x-slot:footer>
        </x-data-table>
    </div>

    {{-- Modal Konfirmasi Hapus Agenda Component --}}
    <x-modal
        name="delete"
        id="modalDeleteKalender"
        form-id="formDeleteKalender"
        cancel-btn-id="btnCancelDeleteKalender"
        confirm-btn-id="btnConfirmDeleteKalender"
        title="Hapus Agenda Kalender"
        message-id="deleteKalenderMessage"
        badge-id="deleteKalenderBadge"
        item-title-id="deleteKalenderTitle"
        >        
        <x-slot:hiddenInputs>
            <input type="hidden" name="view" value="{{ $activeView }}">
        </x-slot:hiddenInputs>
    </x-modal>

    <!-- Modal Tambah Agenda Component -->
    @include('konfigurasi-sekolah.kalender-akademik.create')

    <!-- Modal Ubah Agenda Component -->
    @include('konfigurasi-sekolah.kalender-akademik.edit')

    <!-- Modal Detail Agenda per Tanggal Component -->
    @include('konfigurasi-sekolah.kalender-akademik.detail')

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // ── Data & State ──────────────────────────────────────────────────────────
            const calendarEventsData      = @json($calendarEvents);
            const tahunAjaranSemestersMap = @json($tahunAjaranSemesters);
            const hasTahunAjaranConfigured = {{ $hasTahunAjaran ? 'true' : 'false' }};

            let currentCalendarYear  = {{ $currentYear }};
            let currentCalendarMonth = {{ $currentMonth }};
            let activeViewMode       = '{{ $activeView }}';

            const monthNames = ['Januari','Februari','Maret','April','Mei','Juni',
                                'Juli','Agustus','September','Oktober','November','Desember'];

            // ── DOM Refs ──────────────────────────────────────────────────────────────
            const panelCalendar       = document.getElementById('panelCalendarView');
            const panelTable          = document.getElementById('panelTableView');
            const btnToggleCalendar   = document.getElementById('btnToggleCalendarView');
            const btnToggleTable      = document.getElementById('btnToggleTableView');
            const schoolFilterView    = document.getElementById('schoolFilterView');
            const labelCurrentMonth   = document.getElementById('calendarCurrentMonthLabel');
            const btnPrevMonth        = document.getElementById('btnPrevMonth');
            const btnNextMonth        = document.getElementById('btnNextMonth');
            const btnCalendarToday    = document.getElementById('btnCalendarToday');
            const selectCalendarMonth = document.getElementById('selectCalendarMonth');
            const selectCalendarYear  = document.getElementById('selectCalendarYear');
            const gridBody            = document.getElementById('calendarGridBody');

            // ── View Switcher ─────────────────────────────────────────────────────────
            function switchView(mode) {
                activeViewMode = mode;
                if (schoolFilterView) schoolFilterView.value = mode;
                const isKal = mode === 'kalender';
                panelCalendar.classList.toggle('hidden', !isKal);
                panelTable.classList.toggle('hidden', isKal);
                btnToggleCalendar.classList.toggle('active', isKal);
                btnToggleCalendar.setAttribute('aria-selected', isKal);
                btnToggleTable.classList.toggle('active', !isKal);
                btnToggleTable.setAttribute('aria-selected', !isKal);
            }

            btnToggleCalendar?.addEventListener('click', () => switchView('kalender'));
            btnToggleTable?.addEventListener('click',   () => switchView('tabel'));

            // Sinkronisasi hidden input view saat form submit
            ['formCreateKalender', 'formEditKalender', 'formDeleteKalender'].forEach(id => {
                document.getElementById(id)?.addEventListener('submit', function() {
                    const viewInput = this.querySelector('input[name="view"]');
                    if (viewInput) viewInput.value = activeViewMode;
                });
            });

            // ── Calendar Helpers ──────────────────────────────────────────────────────
            function formatDateYMD(year, month, day) {
                return `${year}-${String(month).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
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

            // Render Grid Kalender dengan Logika Slot Mingguan dan Segmen Rentang Berkelanjutan
            function renderCalendarGrid() {
                if (!gridBody) return;
                gridBody.innerHTML = '';

                labelCurrentMonth.textContent = `${monthNames[currentCalendarMonth - 1]} ${currentCalendarYear}`;
                if (selectCalendarMonth) selectCalendarMonth.value = currentCalendarMonth;
                if (selectCalendarYear) selectCalendarYear.value = currentCalendarYear;

                const today   = new Date();
                const todayStr = formatDateYMD(today.getFullYear(), today.getMonth() + 1, today.getDate());

                let startIdx = new Date(currentCalendarYear, currentCalendarMonth - 1, 1).getDay();
                startIdx = startIdx === 0 ? 6 : startIdx - 1;

                const daysInMonth = new Date(currentCalendarYear, currentCalendarMonth, 0).getDate();
                const daysInPrev  = new Date(currentCalendarYear, currentCalendarMonth - 1, 0).getDate();
                const remaining   = (7 - ((startIdx + daysInMonth) % 7)) % 7;

                const calendarDays = [];
                for (let i = startIdx - 1; i >= 0; i--) {
                    const pm = currentCalendarMonth === 1 ? 12 : currentCalendarMonth - 1;
                    const py = currentCalendarMonth === 1 ? currentCalendarYear - 1 : currentCalendarYear;
                    calendarDays.push({ dayNum: daysInPrev - i, dateStr: formatDateYMD(py, pm, daysInPrev - i), isOtherMonth: true, isToday: false });
                }
                for (let d = 1; d <= daysInMonth; d++) {
                    const dateStr = formatDateYMD(currentCalendarYear, currentCalendarMonth, d);
                    calendarDays.push({ dayNum: d, dateStr, isOtherMonth: false, isToday: dateStr === todayStr });
                }
                for (let d = 1; d <= remaining; d++) {
                    const nm = currentCalendarMonth === 12 ? 1 : currentCalendarMonth + 1;
                    const ny = currentCalendarMonth === 12 ? currentCalendarYear + 1 : currentCalendarYear;
                    calendarDays.push({ dayNum: d, dateStr: formatDateYMD(ny, nm, d), isOtherMonth: true, isToday: false });
                }

                // Proses kalender per minggu (kelompok 7 hari) untuk alokasi slot vertikal yang sejajar
                const maxVisible = 2;
                for (let w = 0; w < calendarDays.length; w += 7) {
                    const weekDays = calendarDays.slice(w, w + 7);

                const seenIds = new Set();
                    const weekEvents = weekDays
                        .flatMap(day => getEventsForDate(day.dateStr))
                        .filter(ev => !seenIds.has(ev.id) && seenIds.add(ev.id));

                    // Multi-day di atas, lalu start asc, end desc, id asc
                    weekEvents.sort((a, b) => {
                        const aM = a.tanggal_mulai !== a.tanggal_selesai ? 1 : 0;
                        const bM = b.tanggal_mulai !== b.tanggal_selesai ? 1 : 0;
                        return (bM - aM)
                            || a.tanggal_mulai.localeCompare(b.tanggal_mulai)
                            || (b.tanggal_selesai || b.tanggal_mulai).localeCompare(a.tanggal_selesai || a.tanggal_mulai)
                            || (a.id - b.id);
                    });

                    const daySlots    = Array.from({ length: 7 }, () => []);
                    const dayOverflow = Array(7).fill(0);

                    weekEvents.forEach(ev => {
                        const start = ev.tanggal_mulai, end = ev.tanggal_selesai || ev.tanggal_mulai;
                        const covered = weekDays.reduce((acc, day, i) => {
                            if (day.dateStr >= start && day.dateStr <= end) acc.push(i);
                            return acc;
                        }, []);
                        if (!covered.length) return;

                        let slot = -1;
                        for (let s = 0; s < maxVisible; s++) {
                            if (covered.every(d => !daySlots[d][s])) { slot = s; break; }
                        }
                        if (slot !== -1) covered.forEach(d => daySlots[d][slot] = ev);
                        else             covered.forEach(d => dayOverflow[d]++);
                    });

                    weekDays.forEach((dayInfo, i) => createDayCellWithSlots(dayInfo, daySlots[i], dayOverflow[i], i));
                }
            }

            function createDayCellWithSlots(dayInfo, slots, overflowCount, dayOfWeekIndex) {
                const { dayNum, dateStr, isOtherMonth, isToday } = dayInfo;
                const eventsOnDate = getEventsForDate(dateStr);
                const isWeekend    = dayOfWeekIndex >= 5;
                const hasHoliday   = eventsOnDate.some(e => e.libur_kbm);

                const cell = document.createElement('div');
                cell.className = ['schedule-day-cell',
                    isWeekend    ? 'is-weekend'     : '',
                    isOtherMonth ? 'is-other-month' : '',
                    isToday      ? 'is-today'       : '',
                    hasHoliday   ? 'is-holiday'     : '',
                ].filter(Boolean).join(' ');
                cell.dataset.date = dateStr;

                // Header sel tanggal
                const header = document.createElement('div');
                header.className = 'schedule-day-header';
                header.innerHTML = `<span class="schedule-day-num${isWeekend ? ' is-weekend' : ''}">${dayNum}</span>`
                    + (hasHoliday ? `<span class="schedule-day-badge">Libur</span>` : '');
                cell.appendChild(header);

                // Daftar item agenda
                const eventsContainer = document.createElement('div');
                eventsContainer.className = 'schedule-items';

                const lastFilled = slots.reduceRight((acc, s, i) => acc === -1 && s ? i : acc, -1);

                for (let s = 0; s <= lastFilled; s++) {
                    const ev = slots[s];
                    if (!ev) {
                        const spacer = document.createElement('div');
                        spacer.className = 'schedule-pill-spacer';
                        eventsContainer.appendChild(spacer);
                        continue;
                    }

                    const isMultiDay = ev.tanggal_mulai && ev.tanggal_selesai && ev.tanggal_mulai !== ev.tanggal_selesai;
                    const pill = document.createElement('div');
                    pill.dataset.calendarEventId = ev.id;

                    if (!isMultiDay) {
                        pill.className = `schedule-pill pill-${ev.warna || 'blue'} pill-span-single`;
                        pill.textContent = ev.judul_kegiatan;
                    } else {
                        const isRowStart = dateStr === ev.tanggal_mulai || dayOfWeekIndex === 0;
                        const isRowEnd   = dateStr === ev.tanggal_selesai || dayOfWeekIndex === 6;
                        const spanType   = (isRowStart && isRowEnd) ? 'single' : isRowStart ? 'start' : isRowEnd ? 'end' : 'middle';
                        pill.className = `schedule-pill pill-${ev.warna || 'blue'} pill-span-${spanType}`;
                        pill.innerHTML = `<span class="schedule-pill-text">${ev.judul_kegiatan}</span>`;
                    }

                    pill.title = `${ev.judul_kegiatan} (${ev.kategori})`;

                    pill.addEventListener('mouseenter', () =>
                        gridBody?.querySelectorAll(`[data-calendar-event-id="${ev.id}"]`)
                            .forEach(el => el.classList.add('is-segment-hovered')));
                    pill.addEventListener('mouseleave', () =>
                        gridBody?.querySelectorAll(`[data-calendar-event-id="${ev.id}"]`)
                            .forEach(el => el.classList.remove('is-segment-hovered')));
                    pill.addEventListener('click', e => { e.stopPropagation(); openEditModal(ev); });

                    eventsContainer.appendChild(pill);
                }

                if (overflowCount > 0) {
                    const more = document.createElement('div');
                    more.className = 'schedule-pill-more';
                    more.textContent = `+${overflowCount} lainnya`;
                    more.title = `Lihat total ${eventsOnDate.length} agenda pada tanggal ini`;
                    more.addEventListener('click', e => { e.stopPropagation(); openDateDetailModal(dateStr, eventsOnDate); });
                    eventsContainer.appendChild(more);
                }

                cell.appendChild(eventsContainer);
                cell.addEventListener('click', () => openDateDetailModal(dateStr, eventsOnDate));
                gridBody.appendChild(cell);
            }

            // ── Navigasi Bulan ────────────────────────────────────────────────────────────
            function changeMonth(delta) {
                currentCalendarMonth += delta;
                if (currentCalendarMonth > 12) { currentCalendarMonth = 1;  currentCalendarYear++; }
                if (currentCalendarMonth < 1)  { currentCalendarMonth = 12; currentCalendarYear--; }
                renderCalendarGrid();
            }

            btnPrevMonth?.addEventListener('click',  () => changeMonth(-1));
            btnNextMonth?.addEventListener('click',  () => changeMonth(1));
            btnCalendarToday?.addEventListener('click', () => {
                const now = new Date();
                currentCalendarYear  = now.getFullYear();
                currentCalendarMonth = now.getMonth() + 1;
                renderCalendarGrid();
            });
            selectCalendarMonth?.addEventListener('change', function() { currentCalendarMonth = +this.value; renderCalendarGrid(); });
            selectCalendarYear?.addEventListener('change',  function() { currentCalendarYear  = +this.value; renderCalendarGrid(); });

            renderCalendarGrid();

            // ========================================================================
            // MANAJEMEN MODAL MENGGUNAKAN InforaModals
            // ========================================================================
            InforaModals.setup('modalCreateKalender', { cancelBtnId: 'btnCancelCreateKalender' });
            InforaModals.setup('modalEditKalender',   { cancelBtnId: 'btnCancelEditKalender' });
            InforaModals.setup('modalDeleteKalender', { cancelBtnId: 'btnCancelDeleteKalender' });
            InforaModals.setup('modalDateDetail',     { cancelBtnId: 'btnDismissDateDetail' });

            // ── Semester Options ──────────────────────────────────────────────────────────
            function updateSemesterOptions(taSelectId, semSelectId, selectedSemester = null) {
                const taSelect  = document.getElementById(taSelectId);
                const semSelect = document.getElementById(semSelectId);
                if (!taSelect || !semSelect) return;

                const semesters = tahunAjaranSemestersMap[taSelect.value] || [];
                const current   = selectedSemester || semSelect.value || 'ganjil';

                semSelect.innerHTML = '';
                if (semesters.length) {
                    semesters.forEach(s => {
                        const opt = new Option(s.label, s.semester);
                        opt.selected = s.semester === current || (s.is_active && !selectedSemester);
                        semSelect.appendChild(opt);
                    });
                } else {
                    semSelect.add(new Option('Ganjil', 'ganjil', false, current === 'ganjil'));
                    semSelect.add(new Option('Genap',  'genap',  false, current === 'genap'));
                }
            }

            document.getElementById('create_tahun_ajaran')?.addEventListener('change', () => updateSemesterOptions('create_tahun_ajaran', 'create_semester'));
            document.getElementById('edit_tahun_ajaran')?.addEventListener('change',   () => updateSemesterOptions('edit_tahun_ajaran', 'edit_semester'));

            // Auto color saat kategori berubah
            function bindAutoColor(selectId, colorSelectId) {
                const sel = document.getElementById(selectId);
                const col = document.getElementById(colorSelectId);
                sel?.addEventListener('change', function() {
                    const color = this.options[this.selectedIndex]?.dataset?.defaultColor;
                    if (color && col) col.value = color;
                });
            }
            bindAutoColor('create_kategori', 'create_warna');
            bindAutoColor('edit_kategori',   'edit_warna');

            // ── Modal Tambah Agenda ─────────────────────────────────────────────────────────
            function openCreateModal(prefillDate = null) {
                if (!hasTahunAjaranConfigured) return;
                if (prefillDate) {
                    document.getElementById('create_tanggal_mulai').value   = prefillDate;
                    document.getElementById('create_tanggal_selesai').value = prefillDate;
                }
                updateSemesterOptions('create_tahun_ajaran', 'create_semester', '{{ $defaultSemester }}');
                InforaModals.open('modalCreateKalender');
            }

            document.getElementById('btnOpenCreateKalender')?.addEventListener('click',  () => openCreateModal());
            document.getElementById('btnEmptyCreateKalender')?.addEventListener('click', () => openCreateModal());

            // ── Modal Rincian Tanggal ──────────────────────────────────────────────────────
            const titleDateDetail    = document.getElementById('modalDateDetailTitle');
            const subtitleDateDetail = document.getElementById('modalDateDetailSubtitle');
            const listDateDetail     = document.getElementById('dateDetailEventsList');
            let activeSelectedDate   = null;

            function openDateDetailModal(dateStr, events) {
                activeSelectedDate = dateStr;
                const [y, m, d] = dateStr.split('-').map(Number);

                if (titleDateDetail) titleDateDetail.textContent = `Agenda: ${d} ${monthNames[m - 1]} ${y}`;
                if (subtitleDateDetail) subtitleDateDetail.textContent = events.length
                    ? `Terdapat ${events.length} kegiatan yang tercatat pada hari ini`
                    : `Tidak ada agenda kegiatan akademik yang terjadwal pada hari ini`;

                if (listDateDetail) {
                    if (!events.length) {
                        listDateDetail.innerHTML = `
                            <div class="empty-state">
                                <x-icon name="info-circle" size="40" class="empty-state-icon" />
                                <span class="empty-state-text">Tidak ada kegiatan akademik pada tanggal ini. Klik tombol di bawah untuk menambahkan agenda baru.</span>
                            </div>`;
                    } else {
                        listDateDetail.innerHTML = '';
                        events.forEach(item => {
                            const div = document.createElement('div');
                            div.className = 'card-list-item';
                            div.innerHTML = `
                                <div class="card-list-item-header">
                                    <span class="card-list-item-title">${item.judul_kegiatan}</span>
                                    <span class="badge ${item.badge_class || 'badge-blue'}">${item.kategori}</span>
                                </div>
                                <div class="card-list-item-meta">
                                    <span><strong>Rentang:</strong> ${item.rentang_formatted || item.tanggal_mulai}</span>
                                    <span>&bull;</span>
                                    <span><strong>Semester:</strong> ${item.semester?.toUpperCase() ?? '-'} (${item.tahun_ajaran})</span>
                                    ${item.libur_kbm ? '<span class="badge badge-rose">Libur KBM</span>' : ''}
                                </div>
                                ${item.keterangan ? `<div class="card-list-item-desc">${item.keterangan}</div>` : ''}
                                <div class="table-actions table-actions-right card-list-item-actions">
                                    <button type="button" class="btn-secondary btn-sm btn-edit-from-detail" title="Ubah Agenda Ini">
                                        <x-icon name="pencil" size="14" />
                                        <span>Ubah Agenda</span>
                                    </button>
                                </div>`;
                            div.querySelector('.btn-edit-from-detail')?.addEventListener('click', () => {
                                InforaModals.close('modalDateDetail');
                                openEditModal(item);
                            });
                            listDateDetail.appendChild(div);
                        });
                    }
                }

                InforaModals.open('modalDateDetail');
            }

            document.getElementById('btnAddEventFromDateDetail')?.addEventListener('click', () => {
                const date = activeSelectedDate;
                InforaModals.close('modalDateDetail');
                openCreateModal(date);
            });

            // ── Modal Ubah Agenda ───────────────────────────────────────────────────────────
            function populateEditModal(data) {
                const form = document.getElementById('formEditKalender');
                if (form && data.id) form.action = `{{ url('konfigurasi-sekolah/kalender-akademik') }}/${data.id}`;

                const fieldMap = {
                    edit_kalender_id:     data.id,
                    edit_school_id:       data.school_id,
                    edit_tahun_ajaran:    data.tahun_ajaran,
                    edit_judul_kegiatan:  data.judul_kegiatan,
                    edit_tanggal_mulai:   data.tanggal_mulai,
                    edit_tanggal_selesai: data.tanggal_selesai || data.tanggal_mulai,
                    edit_kategori:        data.kategori || 'Kegiatan Sekolah',
                    edit_warna:           data.warna || 'blue',
                    edit_keterangan:      data.keterangan,
                };
                Object.entries(fieldMap).forEach(([id, val]) => {
                    const el = document.getElementById(id);
                    if (el) el.value = val || '';
                });

                const liburEl = document.getElementById('edit_libur_kbm');
                if (liburEl) liburEl.checked = Boolean(data.libur_kbm);

                updateSemesterOptions('edit_tahun_ajaran', 'edit_semester', data.semester || 'ganjil');
            }

            function openEditModal(data) {
                populateEditModal(data);
                InforaModals.open('modalEditKalender');
            }

            InforaModals.bindEditButtons('.btn-open-edit-kalender', 'modalEditKalender', (modal, data) => populateEditModal(data));

            // ── Modal Hapus Agenda ──────────────────────────────────────────────────────────
            InforaModals.bindConfirmButtons('.btn-open-delete-kalender', 'modalDeleteKalender', {
                messageId:   'deleteKalenderMessage',
                badgeId:     'deleteKalenderBadge',
                itemTitleId: 'deleteKalenderTitle',
                message:     'Apakah Anda yakin ingin menghapus agenda berikut?',
                badge:       (btn) => btn.dataset.kategori || '-',
                itemTitle:   (btn) => btn.dataset.title || '-',
            });

            // ========================================================================
            // AUTO REOPEN MODAL JIKA VALIDASI GAGAL
            // ========================================================================
            window.hasValidationErrors = {{ $errors->any() ? 'true' : 'false' }};
            window.errorMethod = @json(old('_method'));
            @if (old('_method') === 'PUT' && old('kalender_id'))
                populateEditModal({
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
                });
            @endif
            InforaModals.reopenOnError({ create: 'modalCreateKalender', edit: 'modalEditKalender' });
        });
    </script>
@endsection
