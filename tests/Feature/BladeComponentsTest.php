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
