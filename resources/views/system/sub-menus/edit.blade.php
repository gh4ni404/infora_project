<!-- Modal Edit Sub-Menu Component -->
<div class="modal-backdrop hidden" id="modalEditSubMenu" role="dialog" aria-modal="true" aria-labelledby="modalEditSubMenuTitle">
    <div class="modal-dialog">
        <div class="modal-header">
            <div>
                <h3 class="modal-title" id="modalEditSubMenuTitle">Formulir Perubahan Sub-Menu</h3>
                <p class="modal-subtitle">Perbarui data navigasi sub-menu dan relasi induk menu dalam platform INFORA</p>
            </div>
            <button type="button" class="modal-close-btn" id="btnCloseEditSubMenu" aria-label="Tutup Formulir">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>
        <form method="POST" action="" id="formEditSubMenu">
            @csrf
            @method('PUT')
            <div class="modal-body">
                <div class="form-group">
                    <label for="edit_submenu_menu_id" class="form-label">Induk Menu <span class="text-danger">*</span></label>
                    <select
                        id="edit_submenu_menu_id"
                        name="menu_id"
                        class="form-select @error('menu_id') border-danger @enderror"
                        required
                    >
                        <option value="">-- Pilih Induk Menu --</option>
                        @foreach ($menus as $menu)
                            <option
                                value="{{ $menu->id }}"
                                data-next-order="{{ \App\Models\SubMenu::nextOrder($menu->id) }}"
                                data-route-prefix="{{ $menu->route_prefix }}"
                            >
                                {{ $menu->module?->name ? $menu->module->name . ' → ' : '' }}{{ $menu->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('menu_id')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                    <div class="form-hint">Sub-menu akan ditampilkan di dalam dropdown accordion menu induk ini.</div>
                </div>

                <div class="form-group">
                    <label for="edit_submenu_name" class="form-label">Nama Sub-Menu <span class="text-danger">*</span></label>
                    <input
                        type="text"
                        id="edit_submenu_name"
                        name="name"
                        class="form-input @error('name') border-danger @enderror"
                        data-transform="title-case"
                        placeholder="Contoh: Modul, Menu, Sub-Menu, Rekapitulasi"
                        value="{{ old('name') }}"
                        required
                    >
                    @error('name')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                    <div class="form-hint">Nama item sub-menu otomatis diformat Capitalize Each Word (Title Case).</div>
                </div>

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
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" x2="12" y1="8" y2="12"></line><line x1="12" x2="12.01" y1="16" y2="16"></line></svg>
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

                <div class="form-group">
                    <label for="edit_submenu_order" class="form-label">Urutan Tampil (Order)</label>
                    <input
                        type="number"
                        id="edit_submenu_order"
                        name="order"
                        class="form-input @error('order') border-danger @enderror"
                        value="{{ old('order', 1) }}"
                        min="1"
                    >
                    @error('order')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                    <div class="form-hint">Urutan numerik dari yang terkecil (1, 2, 3, ...) dalam menu induk.</div>
                </div>

                <div class="form-group">
                    <label class="form-check">
                        <input
                            type="checkbox"
                            id="edit_submenu_is_active"
                            name="is_active"
                            value="1"
                            class="form-check-input"
                            {{ old('is_active', '1') == '1' ? 'checked' : '' }}
                        >
                        <span class="form-check-label">Aktifkan sub-menu ini pada navigasi sistem</span>
                    </label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" id="btnCancelEditSubMenu">Batal</button>
                <button type="submit" class="btn-primary">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                        <polyline points="17 21 17 13 7 13 7 21"></polyline>
                        <polyline points="7 3 7 8 15 8"></polyline>
                    </svg>
                    <span>Simpan Perubahan</span>
                </button>
            </div>
        </form>
    </div>
</div>
