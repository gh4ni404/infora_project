<?php

use App\Models\KalenderAkademik;
use App\Models\School;
use App\Models\User;

beforeEach(function () {
    $this->superAdmin = User::factory()->create([
        'username' => 'superadmin_kalender_tester',
        'email' => 'superadmin_kalender@infora.test',
        'user_type' => 'super_admin',
        'is_active' => true,
    ]);

    $this->regularUser = User::factory()->create([
        'username' => 'guru_kalender_tester',
        'email' => 'guru_kalender@infora.test',
        'user_type' => 'guru',
        'is_active' => true,
    ]);

    $this->school = School::first() ?? School::factory()->create([
        'name' => 'SMK Negeri 8 Bone',
        'npsn' => '69944581',
        'school_type' => 'SMK',
        'status' => 'Negeri',
    ]);
});

test('guest is redirected to login when accessing kalender akademik', function () {
    $response = $this->get(route('konfigurasi-sekolah.kalender-akademik'));

    $response->assertRedirect(route('login'));
});

test('non super admin cannot access kalender akademik', function () {
    $response = $this->actingAs($this->regularUser)->get(route('konfigurasi-sekolah.kalender-akademik'));

    $response->assertForbidden();
});

test('super admin can view kalender akademik page with calendar and table views', function () {
    $response = $this->actingAs($this->superAdmin)->get(route('konfigurasi-sekolah.kalender-akademik'));

    $response->assertOk();
    $response->assertSee('Kalender Akademik');
    $response->assertSee('panelCalendarView', false);
    $response->assertSee('panelTableView', false);
    $response->assertSee('modalCreateKalender', false);
    $response->assertSee('modalEditKalender', false);
    $response->assertSee('modalDateDetail', false);
});

test('super admin can view academic calendar events in list', function () {
    $event = KalenderAkademik::create([
        'school_id' => $this->school->id,
        'judul_kegiatan' => 'Penilaian Tengah Semester (PTS)',
        'tanggal_mulai' => '2026-09-20',
        'tanggal_selesai' => '2026-09-25',
        'tahun_ajaran' => '2026/2027',
        'semester' => 'ganjil',
        'kategori' => 'Ujian/Asesmen',
        'warna' => 'indigo',
        'libur_kbm' => false,
        'keterangan' => 'Ujian tengah semester ganjil semua jenjang',
    ]);

    $response = $this->actingAs($this->superAdmin)->get(route('konfigurasi-sekolah.kalender-akademik', ['view' => 'tabel']));

    $response->assertOk();
    $response->assertSee('Penilaian Tengah Semester (PTS)');
    $response->assertSee('Ujian/Asesmen');
    $response->assertSee('2026/2027');
});

test('super admin can store new academic calendar event', function () {
    $payload = [
        'school_id' => $this->school->id,
        'judul_kegiatan' => 'Masa Pengenalan Lingkungan Sekolah (MPLS)',
        'tanggal_mulai' => '2026-07-15',
        'tanggal_selesai' => '2026-07-17',
        'tahun_ajaran' => '2026/2027',
        'semester' => 'ganjil',
        'kategori' => 'Kegiatan Sekolah',
        'warna' => 'emerald',
        'libur_kbm' => '0',
        'keterangan' => 'Penyambutan peserta didik baru tahun ajaran 2026/2027',
    ];

    $response = $this->actingAs($this->superAdmin)
        ->post(route('konfigurasi-sekolah.kalender-akademik.store'), $payload);

    $response->assertRedirect(route('konfigurasi-sekolah.kalender-akademik', [
        'school_id' => $this->school->id,
        'view' => 'kalender',
    ]));
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('kalender_akademik', [
        'school_id' => $this->school->id,
        'judul_kegiatan' => 'Masa Pengenalan Lingkungan Sekolah (MPLS)',
        'tahun_ajaran' => '2026/2027',
        'semester' => 'ganjil',
        'kategori' => 'Kegiatan Sekolah',
        'warna' => 'emerald',
    ]);
});

test('validation fails when required fields are missing', function () {
    $response = $this->actingAs($this->superAdmin)
        ->post(route('konfigurasi-sekolah.kalender-akademik.store'), []);

    $response->assertSessionHasErrors([
        'school_id',
        'judul_kegiatan',
        'tanggal_mulai',
        'tanggal_selesai',
        'tahun_ajaran',
        'semester',
        'kategori',
        'warna',
    ]);
});

test('validation fails when tanggal_selesai is before tanggal_mulai', function () {
    $response = $this->actingAs($this->superAdmin)
        ->post(route('konfigurasi-sekolah.kalender-akademik.store'), [
            'school_id' => $this->school->id,
            'judul_kegiatan' => 'Ujian Sekolah',
            'tanggal_mulai' => '2026-10-10',
            'tanggal_selesai' => '2026-10-05',
            'tahun_ajaran' => '2026/2027',
            'semester' => 'ganjil',
            'kategori' => 'Ujian/Asesmen',
            'warna' => 'indigo',
        ]);

    $response->assertSessionHasErrors(['tanggal_selesai']);
});

test('super admin can update existing academic calendar event', function () {
    $event = KalenderAkademik::create([
        'school_id' => $this->school->id,
        'judul_kegiatan' => 'Libur Semester Ganjil',
        'tanggal_mulai' => '2026-12-21',
        'tanggal_selesai' => '2026-12-31',
        'tahun_ajaran' => '2026/2027',
        'semester' => 'ganjil',
        'kategori' => 'Libur Semester',
        'warna' => 'amber',
        'libur_kbm' => true,
    ]);

    $updatePayload = [
        'school_id' => $this->school->id,
        'judul_kegiatan' => 'Libur Akhir Semester Ganjil',
        'tanggal_mulai' => '2026-12-22',
        'tanggal_selesai' => '2027-01-02',
        'tahun_ajaran' => '2026/2027',
        'semester' => 'ganjil',
        'kategori' => 'Libur Semester',
        'warna' => 'rose',
        'libur_kbm' => '1',
        'keterangan' => 'Diperpanjang sampai awal tahun baru',
    ];

    $response = $this->actingAs($this->superAdmin)
        ->put(route('konfigurasi-sekolah.kalender-akademik.update', $event), $updatePayload);

    $response->assertRedirect(route('konfigurasi-sekolah.kalender-akademik', [
        'school_id' => $this->school->id,
        'view' => 'kalender',
    ]));
    $response->assertSessionHas('success');

    $event->refresh();
    expect($event->judul_kegiatan)->toBe('Libur Akhir Semester Ganjil')
        ->and($event->warna)->toBe('rose')
        ->and($event->libur_kbm)->toBeTrue();
});

test('edit action redirects to index with school_id', function () {
    $event = KalenderAkademik::create([
        'school_id' => $this->school->id,
        'judul_kegiatan' => 'Rapat Pleno Dewan Guru',
        'tanggal_mulai' => '2026-08-01',
        'tanggal_selesai' => '2026-08-01',
        'tahun_ajaran' => '2026/2027',
        'semester' => 'ganjil',
        'kategori' => 'Kegiatan Sekolah',
        'warna' => 'emerald',
    ]);

    $response = $this->actingAs($this->superAdmin)->get(route('konfigurasi-sekolah.kalender-akademik.edit', $event));

    $response->assertRedirect(route('konfigurasi-sekolah.kalender-akademik', [
        'school_id' => $this->school->id,
    ]));
});

test('super admin can delete academic calendar event', function () {
    $event = KalenderAkademik::create([
        'school_id' => $this->school->id,
        'judul_kegiatan' => 'Event Yang Akan Dihapus',
        'tanggal_mulai' => '2026-09-01',
        'tanggal_selesai' => '2026-09-02',
        'tahun_ajaran' => '2026/2027',
        'semester' => 'ganjil',
        'kategori' => 'Kegiatan Sekolah',
        'warna' => 'blue',
    ]);

    $response = $this->actingAs($this->superAdmin)
        ->delete(route('konfigurasi-sekolah.kalender-akademik.destroy', $event));

    $response->assertRedirect(route('konfigurasi-sekolah.kalender-akademik', [
        'school_id' => $this->school->id,
        'view' => 'kalender',
    ]));
    $response->assertSessionHas('success');

    $this->assertDatabaseMissing('kalender_akademik', [
        'id' => $event->id,
    ]);
});

test('events endpoint returns pure json events for calendar', function () {
    KalenderAkademik::create([
        'school_id' => $this->school->id,
        'judul_kegiatan' => 'Uji Kompetensi Keahlian (UKK)',
        'tanggal_mulai' => '2026-03-10',
        'tanggal_selesai' => '2026-03-15',
        'tahun_ajaran' => '2026/2027',
        'semester' => 'genap',
        'kategori' => 'Khusus SMK',
        'warna' => 'purple',
    ]);

    $response = $this->actingAs($this->superAdmin)
        ->getJson(route('konfigurasi-sekolah.kalender-akademik.events', [
            'school_id' => $this->school->id,
            'tahun' => 2026,
            'bulan' => 3,
        ]));

    $response->assertOk();
    $response->assertJsonStructure([
        'success',
        'data' => [
            '*' => [
                'id',
                'school_id',
                'judul_kegiatan',
                'tanggal_mulai',
                'tanggal_selesai',
                'tahun_ajaran',
                'semester',
                'kategori',
                'warna',
                'libur_kbm',
            ],
        ],
    ]);
    $response->assertJsonFragment(['judul_kegiatan' => 'Uji Kompetensi Keahlian (UKK)']);
});

test('filtering agenda by category and semester works correctly', function () {
    KalenderAkademik::create([
        'school_id' => $this->school->id,
        'judul_kegiatan' => 'KBM Semester Ganjil Pekan 1',
        'tanggal_mulai' => '2026-07-20',
        'tanggal_selesai' => '2026-07-25',
        'tahun_ajaran' => '2026/2027',
        'semester' => 'ganjil',
        'kategori' => 'KBM Efektif',
        'warna' => 'blue',
    ]);

    KalenderAkademik::create([
        'school_id' => $this->school->id,
        'judul_kegiatan' => 'Penilaian Akhir Tahun (PAT)',
        'tanggal_mulai' => '2027-06-05',
        'tanggal_selesai' => '2027-06-12',
        'tahun_ajaran' => '2026/2027',
        'semester' => 'genap',
        'kategori' => 'Ujian/Asesmen',
        'warna' => 'indigo',
    ]);

    // Filter KBM Efektif
    $response = $this->actingAs($this->superAdmin)
        ->get(route('konfigurasi-sekolah.kalender-akademik', [
            'school_id' => $this->school->id,
            'view' => 'tabel',
            'kategori' => 'KBM Efektif',
        ]));

    $response->assertOk();
    $agendaTitles = $response->viewData('agendaList')->pluck('judul_kegiatan');
    expect($agendaTitles)->toContain('KBM Semester Ganjil Pekan 1')
        ->not->toContain('Penilaian Akhir Tahun (PAT)');

    // Filter Semester Genap
    $responseGenap = $this->actingAs($this->superAdmin)
        ->get(route('konfigurasi-sekolah.kalender-akademik', [
            'school_id' => $this->school->id,
            'view' => 'tabel',
            'semester' => 'genap',
        ]));

    $responseGenap->assertOk();
    $genapTitles = $responseGenap->viewData('agendaList')->pluck('judul_kegiatan');
    expect($genapTitles)->toContain('Penilaian Akhir Tahun (PAT)')
        ->not->toContain('KBM Semester Ganjil Pekan 1');
});
