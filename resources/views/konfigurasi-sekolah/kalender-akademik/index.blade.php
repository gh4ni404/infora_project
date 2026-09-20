@extends('layouts.app')

@section('content')
    <x-page-header title="Kalender Akademik">
        <x-slot:subtitle>
            Manajemen agenda kegiatan sekolah, jadwal ujian, rentang semester, dan hari libur
            @if ($selectedSchool)
                &ndash; <strong class="text-primary">{{ $selectedSchool->name }}</strong>
            @endif
        </x-slot:subtitle>

        <x-slot:actions>
            {{-- Selector Unit Sekolah --}}
            @if ($schools->count() > 1)
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
            <button type="button" class="btn-primary" id="btnOpenCreateKalender">
                <x-icon name="add" size="18" />
                <span>Tambah Agenda</span>
            </button>
        </x-slot:actions>
    </x-page-header>
    {{-- Flash Alerts --}}
    <x-alert type="success" />
    <x-alert type="error" />
    <x-alert type="validation" />

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
                <input type="hidden" name="school_id" value="{{ $selectedSchoolId }}">

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
                    <th>Aksi</th>
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
                                <button type="button" class="btn-edit btn-open-edit-kalender"
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
                                    data-action="{{ route('konfigurasi-sekolah.kalender-akademik.update', $item) }}">
                                    <x-icon name="pencil" size="14" />
                                </button>
                                <button type="button" class="btn-delete btn-open-delete-kalender"
                                    title="Hapus Agenda" data-title="{{ $item->judul_kegiatan }}"
                                    data-kategori="{{ $item->kategori }}"
                                    data-action="{{ route('konfigurasi-sekolah.kalender-akademik.destroy', $item) }}">
                                    <x-icon name="trash" size="14" />
                                </button>
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

            // Render Grid Kalender dengan Logika Slot Mingguan & Segmen Rentang Berkelanjutan
            function renderCalendarGrid() {
                if (!gridBody) return;
                gridBody.innerHTML = '';

                labelCurrentMonth.textContent = `${monthNames[currentCalendarMonth - 1]} ${currentCalendarYear}`;
                if (selectCalendarMonth) selectCalendarMonth.value = currentCalendarMonth;
                if (selectCalendarYear) selectCalendarYear.value = currentCalendarYear;

                const firstDayOfMonth = new Date(currentCalendarYear, currentCalendarMonth - 1, 1);
                const daysInCurrentMonth = new Date(currentCalendarYear, currentCalendarMonth, 0).getDate();

                // Hari dalam seminggu (0 = Minggu, 1 = Senin, ..., 6 = Sabtu)
                // Di Indonesia kalender dimulai dari Senin (index 0) sampai Minggu (index 6)
                let startingDayIndex = firstDayOfMonth.getDay();
                startingDayIndex = startingDayIndex === 0 ? 6 : startingDayIndex - 1; // 0 for Senin, 6 for Minggu

                // Hari di bulan sebelumnya
                const prevMonthDate = new Date(currentCalendarYear, currentCalendarMonth - 1, 0);
                const daysInPrevMonth = prevMonthDate.getDate();

                const todayObj = new Date();
                const todayString = formatDateYMD(todayObj.getFullYear(), todayObj.getMonth() + 1, todayObj.getDate());

                // Kumpulkan seluruh data sel kalender (bulan sebelumnya, bulan aktif, bulan berikutnya)
                const calendarDays = [];

                // 1. Sel dari bulan sebelumnya
                for (let i = startingDayIndex - 1; i >= 0; i--) {
                    const dayNum = daysInPrevMonth - i;
                    const prevMonth = currentCalendarMonth === 1 ? 12 : currentCalendarMonth - 1;
                    const prevYear = currentCalendarMonth === 1 ? currentCalendarYear - 1 : currentCalendarYear;
                    const dateStr = formatDateYMD(prevYear, prevMonth, dayNum);

                    calendarDays.push({
                        dayNum,
                        dateStr,
                        isOtherMonth: true,
                        isToday: false
                    });
                }

                // 2. Sel bulan saat ini
                for (let dayNum = 1; dayNum <= daysInCurrentMonth; dayNum++) {
                    const dateStr = formatDateYMD(currentCalendarYear, currentCalendarMonth, dayNum);
                    const isToday = (dateStr === todayString);

                    calendarDays.push({
                        dayNum,
                        dateStr,
                        isOtherMonth: false,
                        isToday
                    });
                }

                // 3. Sel bulan berikutnya agar genap kelipatan 7
                const totalCellsSoFar = startingDayIndex + daysInCurrentMonth;
                const remainingCells = (7 - (totalCellsSoFar % 7)) % 7;
                for (let dayNum = 1; dayNum <= remainingCells; dayNum++) {
                    const nextMonth = currentCalendarMonth === 12 ? 1 : currentCalendarMonth + 1;
                    const nextYear = currentCalendarMonth === 12 ? currentCalendarYear + 1 : currentCalendarYear;
                    const dateStr = formatDateYMD(nextYear, nextMonth, dayNum);

                    calendarDays.push({
                        dayNum,
                        dateStr,
                        isOtherMonth: true,
                        isToday: false
                    });
                }

                // Proses kalender per minggu (kelompok 7 hari) untuk alokasi slot vertikal yang sejajar
                const maxVisible = 2;
                for (let w = 0; w < calendarDays.length; w += 7) {
                    const weekDays = calendarDays.slice(w, w + 7);

                    // A. Kumpulkan semua event unik yang aktif pada minggu ini
                    const activeEventsInWeek = [];
                    const seenEventIds = new Set();
                    weekDays.forEach(day => {
                        const dayEvents = getEventsForDate(day.dateStr);
                        dayEvents.forEach(evt => {
                            if (!seenEventIds.has(evt.id)) {
                                seenEventIds.add(evt.id);
                                activeEventsInWeek.push(evt);
                            }
                        });
                    });

                    // B. Prioritaskan event berentang panjang (multi-day) agar berada di slot atas
                    activeEventsInWeek.sort((a, b) => {
                        const aIsMulti = (a.tanggal_mulai && a.tanggal_selesai && a.tanggal_mulai !== a.tanggal_selesai) ? 1 : 0;
                        const bIsMulti = (b.tanggal_mulai && b.tanggal_selesai && b.tanggal_mulai !== b.tanggal_selesai) ? 1 : 0;
                        if (aIsMulti !== bIsMulti) {
                            return bIsMulti - aIsMulti;
                        }
                        if (a.tanggal_mulai !== b.tanggal_mulai) {
                            return a.tanggal_mulai.localeCompare(b.tanggal_mulai);
                        }
                        const aEnd = a.tanggal_selesai || a.tanggal_mulai;
                        const bEnd = b.tanggal_selesai || b.tanggal_mulai;
                        if (aEnd !== bEnd) {
                            return bEnd.localeCompare(aEnd);
                        }
                        return a.id - b.id;
                    });

                    // C. Alokasikan slot konsisten (0 s.d. maxVisible - 1) untuk tiap hari dalam minggu
                    const daySlots = Array.from({ length: 7 }, () => []);
                    const dayOverflow = Array(7).fill(0);

                    activeEventsInWeek.forEach(event => {
                        const start = event.tanggal_mulai;
                        const end = event.tanggal_selesai || event.tanggal_mulai;

                        // Cari indeks hari (0 s.d. 6) yang dicakup oleh event di minggu ini
                        const coveredIndices = [];
                        for (let d = 0; d < 7; d++) {
                            const dStr = weekDays[d].dateStr;
                            if (dStr >= start && dStr <= end) {
                                coveredIndices.push(d);
                            }
                        }
                        if (coveredIndices.length === 0) return;

                        // Cari slot baris terendah yang kosong pada semua hari tersebut
                        let targetSlot = -1;
                        for (let slot = 0; slot < maxVisible; slot++) {
                            const isFree = coveredIndices.every(d => !daySlots[d][slot]);
                            if (isFree) {
                                targetSlot = slot;
                                break;
                            }
                        }

                        if (targetSlot !== -1) {
                            coveredIndices.forEach(d => {
                                daySlots[d][targetSlot] = event;
                            });
                        } else {
                            // Melebihi kapasitas tampilan slot per sel
                            coveredIndices.forEach(d => {
                                dayOverflow[d]++;
                            });
                        }
                    });

                    // D. Render 7 sel hari untuk minggu ini
                    weekDays.forEach((dayInfo, dayIndex) => {
                        createDayCellWithSlots(dayInfo, daySlots[dayIndex], dayOverflow[dayIndex], dayIndex);
                    });
                }
            }

            function createDayCellWithSlots(dayInfo, slots, overflowCount, dayOfWeekIndex) {
                const { dayNum, dateStr, isOtherMonth, isToday } = dayInfo;
                const eventsOnDate = getEventsForDate(dateStr);
                const hasHoliday = eventsOnDate.some(e => e.libur_kbm);
                const isWeekend = (dayOfWeekIndex === 5 || dayOfWeekIndex === 6); // 5 = Sabtu, 6 = Minggu

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

                // Daftar item agenda / jadwal
                const eventsContainer = document.createElement('div');
                eventsContainer.className = 'schedule-items';

                // Tentukan slot teratas yang terisi pada hari ini
                let lastFilledSlot = -1;
                for (let s = slots.length - 1; s >= 0; s--) {
                    if (slots[s]) {
                        lastFilledSlot = s;
                        break;
                    }
                }

                // Render slot secara sejajar dari 0 sampai lastFilledSlot
                for (let s = 0; s <= lastFilledSlot; s++) {
                    const event = slots[s];
                    if (!event) {
                        // Spacer transparan untuk mempertahankan kesejajaran baris horizontal
                        const spacer = document.createElement('div');
                        spacer.className = 'schedule-pill-spacer';
                        eventsContainer.appendChild(spacer);
                        continue;
                    }

                    const isMultiDay = Boolean(event.tanggal_mulai && event.tanggal_selesai && event.tanggal_mulai !== event.tanggal_selesai);
                    const pill = document.createElement('div');
                    pill.dataset.calendarEventId = event.id;

                    if (!isMultiDay) {
                        // Agenda tunggal 1 hari
                        pill.className = `schedule-pill pill-${event.warna || 'blue'} pill-span-single`;
                        pill.textContent = event.judul_kegiatan;
                    } else {
                        // Agenda berentang multi-hari (batang menyatu)
                        const isActualStart = (dateStr === event.tanggal_mulai);
                        const isActualEnd = (dateStr === event.tanggal_selesai);
                        const isRowStart = isActualStart || (dayOfWeekIndex === 0);
                        const isRowEnd = isActualEnd || (dayOfWeekIndex === 6);

                        let spanType = 'middle';
                        if (isRowStart && isRowEnd) {
                            spanType = 'single';
                        } else if (isRowStart) {
                            spanType = 'start';
                        } else if (isRowEnd) {
                            spanType = 'end';
                        }

                        pill.className = `schedule-pill pill-${event.warna || 'blue'} pill-span-${spanType}`;

                        const textSpan = document.createElement('span');
                        textSpan.className = 'schedule-pill-text';
                        textSpan.textContent = event.judul_kegiatan;
                        pill.appendChild(textSpan);
                    }

                    pill.title = `${event.judul_kegiatan} (${event.kategori})`;

                    // Sinkronisasi efek hover untuk semua segmen event yang sama
                    pill.addEventListener('mouseenter', function() {
                        const evId = this.dataset.calendarEventId;
                        if (evId && gridBody) {
                            gridBody.querySelectorAll(`.schedule-pill[data-calendar-event-id="${evId}"]`).forEach(el => {
                                el.classList.add('is-segment-hovered');
                            });
                        }
                    });

                    pill.addEventListener('mouseleave', function() {
                        const evId = this.dataset.calendarEventId;
                        if (evId && gridBody) {
                            gridBody.querySelectorAll(`.schedule-pill[data-calendar-event-id="${evId}"]`).forEach(el => {
                                el.classList.remove('is-segment-hovered');
                            });
                        }
                    });

                    // Klik pill langsung membuka modal edit
                    pill.addEventListener('click', function(e) {
                        e.stopPropagation();
                        openEditModal(event);
                    });

                    eventsContainer.appendChild(pill);
                }

                if (overflowCount > 0) {
                    const moreCount = document.createElement('div');
                    moreCount.className = 'schedule-pill-more';
                    moreCount.textContent = `+${overflowCount} lainnya`;
                    moreCount.title = `Lihat total ${eventsOnDate.length} agenda pada tanggal ini`;
                    moreCount.addEventListener('click', function(e) {
                        e.stopPropagation();
                        openDateDetailModal(dateStr, eventsOnDate);
                    });
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
            // MANAJEMEN MODAL MENGGUNAKAN InforaModals
            // ========================================================================
            InforaModals.setup('modalCreateKalender', { cancelBtnId: 'btnCancelCreateKalender' });
            InforaModals.setup('modalDateDetail', { cancelBtnId: 'btnDismissDateDetail' });
            InforaModals.setup('modalEditKalender', { cancelBtnId: 'btnCancelEditKalender' });
            InforaModals.setup('modalDeleteKalender', { cancelBtnId: 'btnCancelDeleteKalender' });

            // Helper membuka modal tambah dengan opsi prefill tanggal
            function openCreateModal(prefillDate = null) {
                if (prefillDate) {
                    const startInput = document.getElementById('create_tanggal_mulai');
                    const endInput = document.getElementById('create_tanggal_selesai');
                    if (startInput) startInput.value = prefillDate;
                    if (endInput) endInput.value = prefillDate;
                }
                InforaModals.open('modalCreateKalender');
            }

            // Bind tombol buka tambah agenda utama
            InforaModals.bindOpenButton('btnOpenCreateKalender', 'modalCreateKalender');
            InforaModals.bindOpenButton('btnEmptyCreateKalender', 'modalCreateKalender');

            // Kategori change -> Auto color select (Create Modal)
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
            const titleDateDetail = document.getElementById('modalDateDetailTitle');
            const subtitleDateDetail = document.getElementById('modalDateDetailSubtitle');
            const listDateDetail = document.getElementById('dateDetailEventsList');
            const btnAddEventFromDateDetail = document.getElementById('btnAddEventFromDateDetail');
            let activeSelectedDate = null;

            function openDateDetailModal(dateStr, events) {
                activeSelectedDate = dateStr;

                const dateParts = dateStr.split('-');
                const y = parseInt(dateParts[0], 10);
                const m = parseInt(dateParts[1], 10);
                const d = parseInt(dateParts[2], 10);
                const formattedDateText = `${d} ${monthNames[m - 1]} ${y}`;

                if (titleDateDetail) {
                    titleDateDetail.textContent = `Agenda: ${formattedDateText}`;
                }
                if (subtitleDateDetail) {
                    subtitleDateDetail.textContent = events.length > 0 ?
                        `Terdapat ${events.length} kegiatan yang tercatat pada hari ini` :
                        `Tidak ada agenda kegiatan akademik yang terjadwal pada hari ini`;
                }

                if (listDateDetail) {
                    listDateDetail.innerHTML = '';
                    if (events.length === 0) {
                        listDateDetail.innerHTML = `
                            <div class="empty-state">
                                <x-icon name="info-circle" size="40" class="empty-state-icon" />
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
                                        <x-icon name="pencil" size="14" />
                                        <span>Ubah Agenda</span>
                                    </button>
                                </div>
                            `;

                            const editBtn = itemDiv.querySelector('.btn-edit-from-detail');
                            if (editBtn) {
                                editBtn.addEventListener('click', function() {
                                    InforaModals.close('modalDateDetail');
                                    openEditModal(item);
                                });
                            }

                            listDateDetail.appendChild(itemDiv);
                        });
                    }
                }

                InforaModals.open('modalDateDetail');
            }

            btnAddEventFromDateDetail && btnAddEventFromDateDetail.addEventListener('click', function() {
                const dateToPrefill = activeSelectedDate;
                InforaModals.close('modalDateDetail');
                openCreateModal(dateToPrefill);
            });

            // ========================================================================
            // MODAL UBAH AGENDA
            // ========================================================================
            function populateEditModal(eventData) {
                const formEdit = document.getElementById('formEditKalender');
                if (formEdit && eventData.id) {
                    formEdit.action = `{{ url('konfigurasi-sekolah/kalender-akademik') }}/${eventData.id}`;
                }

                const kalenderIdEl = document.getElementById('edit_kalender_id');
                if (kalenderIdEl) kalenderIdEl.value = eventData.id || '';

                const schoolSelect = document.getElementById('edit_school_id');
                if (schoolSelect) schoolSelect.value = eventData.school_id || '';

                const taEl = document.getElementById('edit_tahun_ajaran');
                if (taEl) taEl.value = eventData.tahun_ajaran || '';

                const semEl = document.getElementById('edit_semester');
                if (semEl) semEl.value = eventData.semester || 'ganjil';

                const judulEl = document.getElementById('edit_judul_kegiatan');
                if (judulEl) judulEl.value = eventData.judul_kegiatan || '';

                const tglMulaiEl = document.getElementById('edit_tanggal_mulai');
                if (tglMulaiEl) tglMulaiEl.value = eventData.tanggal_mulai || '';

                const tglSelesaiEl = document.getElementById('edit_tanggal_selesai');
                if (tglSelesaiEl) tglSelesaiEl.value = eventData.tanggal_selesai || eventData.tanggal_mulai || '';

                const katEl = document.getElementById('edit_kategori');
                if (katEl) katEl.value = eventData.kategori || 'Kegiatan Sekolah';

                const warnaEl = document.getElementById('edit_warna');
                if (warnaEl) warnaEl.value = eventData.warna || 'blue';

                const liburEl = document.getElementById('edit_libur_kbm');
                if (liburEl) liburEl.checked = Boolean(eventData.libur_kbm);

                const ketEl = document.getElementById('edit_keterangan');
                if (ketEl) ketEl.value = eventData.keterangan || '';
            }

            function openEditModal(eventData) {
                populateEditModal(eventData);
                InforaModals.open('modalEditKalender');
            }

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

            // Bind tombol edit di tabel menggunakan InforaModals
            InforaModals.bindEditButtons('.btn-open-edit-kalender', 'modalEditKalender', function(modal, eventData) {
                populateEditModal(eventData);
            });

            // ========================================================================
            // MODAL HAPUS AGENDA
            // ========================================================================
            InforaModals.bindDeleteButtons('.btn-open-delete-kalender', 'modalDeleteKalender', function(modal, btn) {
                document.getElementById('deleteKalenderMessage').textContent = 'Apakah Anda yakin ingin menghapus agenda berikut?';
                document.getElementById('deleteKalenderBadge').textContent = btn.dataset.kategori || '-';
                document.getElementById('deleteKalenderTitle').textContent = btn.dataset.title || '-';
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
