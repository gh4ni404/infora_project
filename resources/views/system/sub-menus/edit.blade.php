{{-- Modal Edit Sub-Menu Component --}}
<x-modal
    id="modalEditSubMenu"
    title="Formulir Perubahan Sub-Menu"
    subtitle="Perbarui data navigasi sub-menu dan relasi induk menu dalam platform INFORA"
    size="lg"
    action=""
    method="PUT"
    form-id="formEditSubMenu"
>
    {{-- Induk Menu --}}
    <x-form-select
        name="menu_id"
        id="edit_submenu_menu_id"
        label="Induk Menu"
        placeholder="Pilih Induk Menu"
        hint="Sub-menu akan ditampilkan di dalam dropdown accordion menu induk ini."
        required
    >
        @foreach ($menus as $menu)
            <option
                value="{{ $menu->id }}"
                data-next-order="{{ \App\Models\SubMenu::nextOrder($menu->id) }}"
                data-route-prefix="{{ $menu->route_prefix }}"
            >
                {{ $menu->module?->name ? $menu->module->name . ' → ' : '' }}{{ $menu->name }}
            </option>
        @endforeach
    </x-form-select>

    {{-- Nama Sub-Menu --}}
    <x-form-input
        name="name"
        id="edit_submenu_name"
        label="Nama Sub-Menu"
        placeholder="Contoh: Modul, Menu, Sub-Menu, Rekapitulasi"
        data-transform="title-case"
        hint="Nama item sub-menu otomatis diformat Capitalize Each Word (Title Case)."
        required
    />

    {{-- Nama Rute dan Panduan Format --}}
    <div class="form-group">
        <label class="form-label">Nama Rute (Route Name)</label>
        <div class="flex items-center gap-2">
            <div class="flex-1">
                <input
                    type="text"
                    id="edit_submenu_route_prefix"
                    name="route_prefix"
                    class="form-input font-mono text-sm @error('route_name') border-danger @enderror"
                    placeholder="Prefix (contoh: master)"
                    value="{{ old('route_prefix') }}"
                    autocomplete="off"
                >
            </div>
            <span class="text-xl font-bold text-slate-400 select-none">.</span>
            <div class="flex-1">
                <input
                    type="text"
                    id="edit_submenu_route_suffix"
                    name="route_suffix"
                    class="form-input font-mono text-sm @error('route_name') border-danger @enderror"
                    placeholder="Sub-rute (contoh: data-sekolah)"
                    value="{{ old('route_suffix') }}"
                    autocomplete="off"
                >
            </div>
        </div>
        @error('route_name')
            <div class="form-error">{{ $message }}</div>
        @enderror
        <div class="form-hint flex items-center gap-2 mt-1.5">
            <span>Pratinjau Rute:</span>
            <code class="text-brand font-mono font-semibold" id="edit_submenu_route_preview">-</code>
        </div>
        <div class="route-guide-box">
            <div class="route-guide-title">
                <x-icon name="info" size="16" />
                <span>Panduan Format Rute Sub-Menu</span>
            </div>
            <ul class="route-guide-list">
                <li>Kotak pertama (Prefix) otomatis terisi sesuai nama induk menu dan <strong>tetap dapat diedit</strong> sesuai kebutuhan.</li>
                <li>Kotak kedua diisi nama rute spesifik fitur (contoh: <code>data-sekolah</code>). Jika rute tunggal tanpa prefix (misal <code>dashboard</code>), cukup isi kotak kedua atau kotak pertama saja.</li>
            </ul>
            <div class="route-suggest-pills">
                <span class="route-suggest-label">Pilihan Cepat:</span>
                <button type="button" class="route-suggest-pill" onclick="setEditRoute('system', 'modules')">system.modules</button>
                <button type="button" class="route-suggest-pill" onclick="setEditRoute('system', 'menus')">system.menus</button>
                <button type="button" class="route-suggest-pill" onclick="setEditRoute('system', 'sub-menus')">system.sub-menus</button>
                <button type="button" class="route-suggest-pill" onclick="setEditRoute('', 'dashboard')">dashboard</button>
            </div>
        </div>
    </div>

    {{-- Urutan Tampil --}}
    <x-form-input
        type="number"
        name="order"
        id="edit_submenu_order"
        label="Urutan Tampil (Order)"
        :value="old('order', 1)"
        min="1"
        hint="Urutan numerik dari yang terkecil (1, 2, 3, ...) dalam menu induk."
    />

    {{-- Status Aktif --}}
    <x-form-checkbox
        name="is_active"
        id="edit_submenu_is_active"
        label="Aktifkan sub-menu ini pada navigasi sistem"
        value="1"
        :checked="old('is_active', '1') == '1'"
    />

    {{-- Footer Modal --}}
    <x-slot:footer>
        <button type="button" class="btn-secondary" id="btnCancelEditSubMenu">Batal</button>
        <button type="submit" class="btn-primary">Update</button>
    </x-slot:footer>
</x-modal>
