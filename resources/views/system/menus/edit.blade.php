{{-- Modal Edit Menu --}}
<x-modal
    id="modalEditMenu"
    title="Formulir Perubahan Menu"
    subtitle="Perbarui data navigasi menu dan relasi modul dalam platform INFORA"
    size="lg"
    action=""
    method="PUT"
    form-id="formEditMenu"
>
    {{-- Induk Modul --}}
    <x-form-select
        name="module_id"
        id="edit_menu_module_id"
        label="Induk Modul"
        placeholder="Pilih Induk Modul"
        :options="$modules"
        :option-attributes="fn($module) => ['data-next-order' => \App\Models\Menu::nextOrder($module->id)]"
        hint="Menu akan dikelompokkan di bawah modul yang Anda tentukan."
        required
    />

    {{-- Nama Menu --}}
    <x-form-input
        name="name"
        id="edit_menu_name"
        label="Nama Menu"
        placeholder="Contoh: Dashboard, Sistem, Jurnal KBM, Data Siswa"
        data-transform="title-case"
        hint="Nama item menu otomatis diformat Capitalize Each Word (Title Case)."
        required
    />

    {{-- Nama Rute dan Panduan --}}
    <x-form-input
        name="route_name"
        id="edit_menu_route_name"
        label="Nama Rute (Route Name)"
        placeholder="Contoh: system.modules, master.data, atau dashboard"
        list="edit_menu_routes_list"
    >
        <datalist id="edit_menu_routes_list">
            <option value="dashboard">Dashboard Utama</option>
            <option value="system.modules">Tata Kelola Modul</option>
            <option value="system.menus">Tata Kelola Menu</option>
            <option value="system.sub-menus">Tata Kelola Sub-Menu</option>
        </datalist>

        <div class="route-guide-box">
            <div class="route-guide-title">
                <x-icon name="info" size="16" />
                <span>Panduan Nama Rute</span>
            </div>
            <ul class="route-guide-list">
                <li><strong>Menu Induk (Ber-Submenu):</strong> Biarkan <strong>kosong</strong> jika menu ini hanya menjadi induk dropdown accordion.</li>
                <li><strong>Menu Langsung:</strong> Cukup gunakan format hierarki <code>modul.menu</code> (contoh: <code>system.modules</code>). Sistem otomatis mencocokkan tanpa perlu akhiran <code>.index</code>.</li>
            </ul>
            <div class="route-suggest-pills">
                <span class="route-suggest-label">Pilihan Cepat:</span>
                <button type="button" class="route-suggest-pill" onclick="document.getElementById('edit_menu_route_name').value='dashboard'">dashboard</button>
                <button type="button" class="route-suggest-pill" onclick="document.getElementById('edit_menu_route_name').value='system.modules'">system.modules</button>
                <button type="button" class="route-suggest-pill" onclick="document.getElementById('edit_menu_route_name').value='system.menus'">system.menus</button>
                <button type="button" class="route-suggest-pill" onclick="document.getElementById('edit_menu_route_name').value=''">[Kosongkan - Sebagai Induk]</button>
            </div>
        </div>
    </x-form-input>

    {{-- Ikon Menu (Lucide) --}}
    <div class="form-group">
        <label class="form-label">Ikon Menu (Lucide)</label>
        <x-icon-picker name="icon" :value="old('icon', 'menu')" id="edit_menu_icon" />
        @error('icon')
            <div class="form-error">{{ $message }}</div>
        @enderror
        <div class="form-hint">Pilih ikon visual dari katalog resmi (100% gratis) untuk mewakili menu pada sidebar.</div>
    </div>

    {{-- Urutan Tampil --}}
    <x-form-input
        type="number"
        name="order"
        id="edit_menu_order"
        label="Urutan Tampil (Order)"
        :value="old('order', 1)"
        min="1"
        hint="Urutan numerik dalam modul (1, 2, 3, ...)."
    />

    {{-- Status Aktif --}}
    <x-form-checkbox
        name="is_active"
        id="edit_menu_is_active"
        label="Aktifkan menu ini pada navigasi sistem"
        :checked="old('is_active', '1') == '1'"
    />

    {{-- Footer Modal --}}
    <x-slot:footer>
        <button type="button" class="btn-secondary" id="btnCancelEditMenu">Batal</button>
        <button type="submit" class="btn-primary">Update</button>
    </x-slot:footer>
</x-modal>
