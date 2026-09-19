<?php

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\MessageBag;
use Illuminate\Support\ViewErrorBag;

test('alert renders success message with success styling', function () {
    $view = $this->blade('<x-alert type="success" message="Data berhasil disimpan" />');

    $view->assertSee('alert-success', false);
    $view->assertSee('Data berhasil disimpan');
    $view->assertDontSee('alert-danger', false);
});

test('alert renders error message with danger styling', function () {
    $view = $this->blade('<x-alert type="error" message="Gagal menghapus data" />');

    $view->assertSee('alert-danger', false);
    $view->assertSee('Gagal menghapus data');
});

test('alert renders nothing when no message is provided', function () {
    $view = $this->blade('<x-alert type="success" />');

    expect((string) $view)->not->toContain('alert-success');
});

test('alert renders custom slot content and title', function () {
    $view = $this->blade(
        '<x-alert type="error" title="Peringatan!">
            <span>Pesan khusus slot</span>
        </x-alert>'
    );

    $view->assertSee('alert-danger', false);
    $view->assertSee('Peringatan!');
    $view->assertSee('<span>Pesan khusus slot</span>', false);
});

test('page header renders title, subtitle, and unescaped action slot', function () {
    $view = $this->blade(
        '<x-page-header title="Wilayah Provinsi" subtitle="Kelola data provinsi">
            <x-slot:actions><a href="/tambah" class="btn-primary">Tambah</a></x-slot:actions>
        </x-page-header>'
    );

    $view->assertSee('page-header', false);
    $view->assertSee('Wilayah Provinsi');
    $view->assertSee('Kelola data provinsi');
    $view->assertSee('btn-primary', false);
    $view->assertSee('href="/tambah"', false);
});

test('search toolbar renders the filter form with search input and per-page selector', function () {
    $view = $this->blade(
        '<x-search-toolbar action="/wilayah/provinsi" search-placeholder="Cari provinsi..." per-page="15" />'
    );

    $view->assertSee('table-toolbar-form', false);
    $view->assertSee('action="/wilayah/provinsi"', false);
    $view->assertSee('Cari provinsi...');
    $view->assertSee('per-page-selector', false);
    $view->assertSee('name="search"', false);
});

test('search toolbar renders customizable total prefix', function () {
    $defaultView = $this->blade(
        '<x-search-toolbar :total="10" total-label="siswa" />'
    );
    $defaultView->assertSee('Total:');
    $defaultView->assertSee('10');
    $defaultView->assertSee('siswa');

    $customView = $this->blade(
        '<x-search-toolbar :total="10" total-label="siswa" total-prefix="Jumlah Keseluruhan:" />'
    );
    $customView->assertSee('Jumlah Keseluruhan:');
    $customView->assertDontSee('Total:');
});

test('data table renders custom filters slot inside search toolbar', function () {
    $view = $this->blade(
        '<x-data-table route="/test-route" :per-page="15">
            <x-slot:filters>
                <select name="kategori"><option value="ujian">Ujian</option></select>
            </x-slot:filters>
            <thead><tr><th>Nama</th></tr></thead>
            <tbody><tr><td>Data</td></tr></tbody>
        </x-data-table>'
    );

    $view->assertSee('table-toolbar-form', false);
    $view->assertSee('name="kategori"', false);
    $view->assertSee('value="ujian"', false);
    $view->assertSee('Ujian');
    $view->assertSee('table-responsive', false);
    $view->assertSee('data-table', false);
});

test('data table renders custom toolbar slot when provided', function () {
    $view = $this->blade(
        '<x-data-table id="customCardTable">
            <x-slot:toolbar>
                <span class="custom-title">Judul Tabel Khusus</span>
                <button type="button" class="btn-primary">Tambah</button>
            </x-slot:toolbar>
            <thead><tr><th>Nama</th></tr></thead>
            <tbody><tr><td>Data</td></tr></tbody>
        </x-data-table>'
    );

    $view->assertSee('id="customCardTable"', false);
    $view->assertSee('table-toolbar', false);
    $view->assertSee('Judul Tabel Khusus');
    $view->assertSee('Tambah');
    $view->assertDontSee('table-toolbar-form', false);
    $view->assertSee('table-responsive', false);
    $view->assertSee('data-table', false);
});

test('data table renders title preset toolbar with total and actions slot', function () {
    $view = $this->blade(
        '<x-data-table title="Daftar Guru" :total="25" total-label="Orang">
            <x-slot:actions>
                <button type="button" class="btn-primary">Tambah Guru</button>
            </x-slot:actions>
            <thead><tr><th>Nama</th></tr></thead>
            <tbody><tr><td>Data</td></tr></tbody>
        </x-data-table>'
    );

    $view->assertSee('table-toolbar', false);
    $view->assertSee('Daftar Guru');
    $view->assertSee('25 Orang');
    $view->assertSee('Tambah Guru');
    $view->assertDontSee('table-toolbar-form', false);
});

test('data table renders title preset toolbar with subtitle slot', function () {
    $view = $this->blade(
        '<x-data-table title="Daftar Semester">
            <x-slot:subtitle>
                Tahun: <strong>2026/2027</strong>
            </x-slot:subtitle>
            <thead><tr><th>Semester</th></tr></thead>
            <tbody><tr><td>Ganjil</td></tr></tbody>
        </x-data-table>'
    );

    $view->assertSee('table-toolbar', false);
    $view->assertSee('Daftar Semester');
    $view->assertSee('Tahun: <strong>2026/2027</strong>', false);
    $view->assertDontSee('table-toolbar-form', false);
});

test('modal renders delete form with csrf method override and html body and footer slots', function () {
    $view = $this->blade(
        '<x-modal id="modalDelete" title="Konfirmasi Hapus" method="DELETE">
            <p>Yakin ingin menghapus <b>provinsi</b> ini?</p>
            <x-slot:footer>
                <button type="button" class="btn-secondary">Batal</button>
                <button type="submit" class="btn-delete">Hapus</button>
            </x-slot:footer>
        </x-modal>'
    );

    $view->assertSee('modal-backdrop', false);
    $view->assertSee('id="modalDelete"', false);
    $view->assertSee('name="_method" value="DELETE"', false);
    $view->assertSee('Yakin ingin menghapus');
    $view->assertSee('<b>provinsi</b>', false);
    $view->assertSee('btn-delete', false);
    $view->assertDontSee('&lt;button', false);
});

test('modal renders delete variant with preset danger, size sm, alert, and footer buttons', function () {
    $view = $this->blade(
        '<x-modal name="delete" id="modalDeleteTest" item-name="jurusan" />'
    );

    $view->assertSee('modal-backdrop', false);
    $view->assertSee('modal-sm', false);
    $view->assertSee('text-danger', false);
    $view->assertSee('name="_method" value="DELETE"', false);
    $view->assertSee('Apakah Anda yakin ingin menghapus jurusan');
    $view->assertSee('Tindakan ini tidak dapat dibatalkan.');
    $view->assertSee('btn-delete', false);
});

test('modal renders a plain dialog when no form method is given', function () {
    $view = $this->blade(
        '<x-modal id="modalInfo" title="Informasi"><p>Konten dialog biasa.</p></x-modal>'
    );

    $view->assertSee('modal-header', false);
    $view->assertSee('Informasi');
    $view->assertDontSee('<form', false);
});

test('empty state renders icon, title, text, and action inside a table row', function () {
    $view = $this->blade(
        '<x-empty-state colspan="6" title="Belum Ada Data" text="Silakan tambahkan data baru.">
            <x-slot:action><a href="/tambah" class="btn-primary">Tambah Data</a></x-slot:action>
        </x-empty-state>'
    );

    $view->assertSee('colspan="6"', false);
    $view->assertSee('empty-state', false);
    $view->assertSee('Belum Ada Data');
    $view->assertSee('Silakan tambahkan data baru.');
    $view->assertSee('href="/tambah"', false);
});

test('table actions renders the action container with given alignment', function () {
    $view = $this->blade(
        '<x-table-actions align="right"><button class="btn-edit">Edit</button></x-table-actions>'
    );

    $view->assertSee('table-actions table-actions-right', false);
    $view->assertSee('btn-edit', false);
});

test('pagination summary renders the shared pagination component inside a table footer', function () {
    $paginator = new LengthAwarePaginator(collect(range(1, 15)), 50, 15, 1, [
        'path' => route('system.modules.index'),
    ]);

    $view = $this->blade('<x-pagination-summary :paginator="$paginator" />', ['paginator' => $paginator]);

    $view->assertSee('table-footer', false);
    $view->assertSee('pagination-wrapper', false);
    $view->assertSee('pagination-nav', false);
    $view->assertSee('Menampilkan', false);
});

test('form-input renders label, hint, required star, and native input attributes', function () {
    $view = $this->blade(
        '<x-form-input
            name="kode"
            label="Kode Jurusan"
            placeholder="Contoh: RPL"
            value="RPL"
            hint="Kode unik pengenal jurusan."
            required
            maxlength="20"
        />'
    );

    $view->assertSee('form-group', false);
    $view->assertSee('Kode Jurusan');
    $view->assertSee('<span class="text-danger">*</span>', false);
    $view->assertSee('name="kode"', false);
    $view->assertSee('id="kode"', false);
    $view->assertSee('value="RPL"', false);
    $view->assertSee('placeholder="Contoh: RPL"', false);
    $view->assertSee('maxlength="20"', false);
    $view->assertSee('required', false);
    $view->assertSee('Kode unik pengenal jurusan.');
    $view->assertDontSee('border-danger', false);
});

test('form-input renders validation error message and error border when session has error', function () {
    $errors = new ViewErrorBag;
    $messageBag = new MessageBag(['kode' => ['Kode jurusan wajib diisi.']]);
    $errors->put('default', $messageBag);
    view()->share('errors', $errors);

    $view = $this->blade(
        '<x-form-input name="kode" label="Kode Jurusan" />'
    );

    $view->assertSee('border-danger', false);
    $view->assertSee('form-error', false);
    $view->assertSee('Kode jurusan wajib diisi.');
});

test('form-input renders hidden input cleanly without form-group wrapper', function () {
    $view = $this->blade('<x-form-input type="hidden" name="school_id" value="5" />');

    $view->assertSee('type="hidden"', false);
    $view->assertSee('name="school_id"', false);
    $view->assertSee('value="5"', false);
    $view->assertDontSee('form-group', false);
});

test('form-select renders options, placeholder, label, and required indicator', function () {
    $view = $this->blade(
        '<x-form-select name="jenjang" label="Jenjang Pendidikan" placeholder="-- Pilih Jenjang --" required>
            <option value="SMK">SMK</option>
            <option value="SMA">SMA</option>
        </x-form-select>'
    );

    $view->assertSee('form-select', false);
    $view->assertSee('Jenjang Pendidikan');
    $view->assertSee('<span class="text-danger">*</span>', false);
    $view->assertSee('name="jenjang"', false);
    $view->assertSee('-- Pilih Jenjang --');
    $view->assertSee('<option value="SMK">SMK</option>', false);
    $view->assertSee('<option value="SMA">SMA</option>', false);
});

test('form-select renders options from options array prop and marks selected', function () {
    $options = ['smk' => 'SMK Negeri 1', 'sma' => 'SMA Negeri 2'];

    $view = $this->blade(
        '<x-form-select name="unit" :options="$options" selected="sma" />',
        ['options' => $options]
    );

    $view->assertSee('value="smk"', false);
    $view->assertSee('SMK Negeri 1');
    $view->assertSee('value="sma"', false);
    $view->assertSee('selected', false);
    $view->assertSee('SMA Negeri 2');
});

test('form-select renders options from nested array with label key', function () {
    $options = [
        'kbm' => ['label' => 'KBM Efektif', 'color' => 'blue'],
        'ujian' => ['label' => 'Ujian Akhir', 'color' => 'red'],
    ];

    $view = $this->blade(
        '<x-form-select name="kategori" :options="$options" selected="kbm" placeholder="Semua Kategori" />',
        ['options' => $options]
    );

    $view->assertSee('Semua Kategori');
    $view->assertSee('value="kbm"', false);
    $view->assertSee('selected', false);
    $view->assertSee('KBM Efektif');
    $view->assertSee('value="ujian"', false);
    $view->assertSee('Ujian Akhir');
});

test('form-textarea renders multiline text, rows, and hint', function () {
    $view = $this->blade(
        '<x-form-textarea
            name="deskripsi"
            label="Deskripsi Jurusan"
            rows="5"
            value="Catatan kurikulum"
            hint="Maksimal 1000 karakter."
        />'
    );

    $view->assertSee('form-textarea', false);
    $view->assertSee('rows="5"', false);
    $view->assertSee('Deskripsi Jurusan');
    $view->assertSee('Catatan kurikulum');
    $view->assertSee('Maksimal 1000 karakter.');
});

test('form-checkbox renders check input with label and checked state', function () {
    $view = $this->blade(
        '<x-form-checkbox
            name="is_active"
            label="Status Jurusan Aktif"
            value="1"
            :checked="true"
        />'
    );

    $view->assertSee('form-check', false);
    $view->assertSee('form-check-input', false);
    $view->assertSee('form-check-label', false);
    $view->assertSee('Status Jurusan Aktif');
    $view->assertSee('name="is_active"', false);
    $view->assertSee('value="1"', false);
    $view->assertSee('checked', false);
});

test('tab-pills renders wrapper with tablist role and accessible label', function () {
    $view = $this->blade(
        '<x-tab-pills label="Pilihan Tampilan">
            <x-tab-pill id="btnTab1" :active="true" icon="calendar">Kalender</x-tab-pill>
            <x-tab-pill id="btnTab2" :active="false" icon="list-bullet">Tabel Agenda</x-tab-pill>
        </x-tab-pills>'
    );

    $view->assertSee('class="tab-pills"', false);
    $view->assertSee('role="tablist"', false);
    $view->assertSee('aria-label="Pilihan Tampilan"', false);
    $view->assertSee('<button type="button"', false);
    $view->assertSee('id="btnTab1"', false);
    $view->assertSee('class="tab-pill active"', false);
    $view->assertSee('aria-selected="true"', false);
    $view->assertSee('Kalender');
    $view->assertSee('id="btnTab2"', false);
    $view->assertSee('class="tab-pill"', false);
    $view->assertSee('aria-selected="false"', false);
    $view->assertSee('Tabel Agenda');
});

test('tab-pill renders anchor tag when href is provided and supports badge', function () {
    $view = $this->blade(
        '<x-tab-pill href="/pengguna" :active="true" badge="12" badge-class="badge-blue">
            Daftar Pengguna
        </x-tab-pill>'
    );

    $view->assertSee('<a href="/pengguna"', false);
    $view->assertSee('class="tab-pill active"', false);
    $view->assertSee('role="tab"', false);
    $view->assertSee('aria-selected="true"', false);
    $view->assertSee('aria-current="page"', false);
    $view->assertSee('Daftar Pengguna');
    $view->assertSee('class="badge badge-blue"', false);
    $view->assertSee('12');
});
