/**
 * PermissionMatrix (Matriks Hak Akses)
 *
 * Modul JavaScript mandiri dan reusable untuk mengelola interaksi tabel matriks perizinan (RBAC).
 *
 * Mengelola:
 * 1. Dependensi Hak Akses Baris (View vs Action):
 *    - Ketika can_view non-aktif, seluruh aksi CRUD (create, edit, delete) otomatis dinonaktifkan dan dicentang ulang (uncheck).
 *    - Ketika can_view aktif, seluruh aksi CRUD diaktifkan kembali.
 *    - Ketika aksi CRUD dicentang, can_view otomatis ikut tercentang.
 * 2. Sinkronisasi Hirarki (Parent Menu vs Sub Menu):
 *    - Jika sub-menu can_view diaktifkan, parent menu can_view otomatis ikut tercentang.
 * 3. Aksi Cepat / Toolbar Masal:
 *    - Centang Semua Lihat (Select All View)
 *    - Centang Semua CRUD (Select All CRUD)
 *    - Kosongkan Semua (Uncheck All)
 * 4. Aksi Cepat per Modul:
 *    - Toggle seluruh can_view pada modul terkait.
 *
 * Penggunaan:
 *   // Inisialisasi otomatis via class/selector default:
 *   PermissionMatrix.init();
 *
 *   // Atau inisialisasi pada container tertentu:
 *   PermissionMatrix.init('#templateForm');
 *   const matrix = new PermissionMatrix(document.getElementById('myContainer'));
 */

export class PermissionMatrix {
    /**
     * @param {HTMLElement|string} container - Elemen kontainer atau selector (default: document)
     * @param {Object} options - Opsi konfigurasi selector
     */
    constructor(container = document, options = {}) {
        this.container = typeof container === 'string'
            ? document.querySelector(container)
            : container;

        if (!this.container) {
            return;
        }

        // Cegah duplikasi inisialisasi pada container yang sama
        if (this.container.__permissionMatrix) {
            return this.container.__permissionMatrix;
        }

        this.options = {
            viewSelector: '.perm-view',
            actionSelector: '.perm-action',
            checkboxSelector: '.matrix-checkbox',
            selectAllViewBtn: '#btnSelectAllView',
            selectAllCrudBtn: '#btnSelectAllCRUD',
            uncheckAllBtn: '#btnUncheckAll',
            moduleToggleBtn: '.btn-check-module',
            targetRowAttr: 'targetRow',
            parentRowAttr: 'parentRow',
            moduleIdAttr: 'moduleId',
            ...options,
        };

        this.container.__permissionMatrix = this;
        this.bindEvents();
    }

    /**
     * Daftarkan seluruh event listener
     */
    bindEvents() {
        this.bindViewCheckboxes();
        this.bindActionCheckboxes();
        this.bindToolbarButtons();
        this.bindModuleToggles();
    }

    /**
     * 1. Hubungan can_view dengan aksi CRUD dan hirarki parent menu
     */
    bindViewCheckboxes() {
        const views = this.container.querySelectorAll(this.options.viewSelector);

        views.forEach((viewBox) => {
            viewBox.addEventListener('change', () => {
                const rowId = viewBox.dataset[this.options.targetRowAttr];
                const isChecked = viewBox.checked;

                if (rowId) {
                    const actionBoxes = this.container.querySelectorAll(
                        `${this.options.actionSelector}[data-target-row="${rowId}"]`
                    );

                    actionBoxes.forEach((box) => {
                        box.disabled = !isChecked;
                        if (!isChecked) {
                            box.checked = false;
                        }
                    });
                }

                // Hirarki: jika sub-menu diaktifkan, otomatis aktifkan parent menu view
                const parentRowId = viewBox.dataset[this.options.parentRowAttr];
                if (isChecked && parentRowId) {
                    const parentView = this.container.querySelector(
                        `${this.options.viewSelector}[data-target-row="${parentRowId}"]`
                    );
                    if (parentView && !parentView.checked) {
                        parentView.checked = true;
                        parentView.dispatchEvent(new Event('change', { bubbles: true }));
                    }
                }
            });
        });
    }

    /**
     * 2. Hubungan aksi CRUD ke can_view (jika aksi dipilih, can_view harus aktif)
     */
    bindActionCheckboxes() {
        const actions = this.container.querySelectorAll(this.options.actionSelector);

        actions.forEach((actionBox) => {
            actionBox.addEventListener('change', () => {
                if (actionBox.checked) {
                    const rowId = actionBox.dataset[this.options.targetRowAttr];
                    if (rowId) {
                        const viewBox = this.container.querySelector(
                            `${this.options.viewSelector}[data-target-row="${rowId}"]`
                        );
                        if (viewBox && !viewBox.checked) {
                            viewBox.checked = true;
                            viewBox.dispatchEvent(new Event('change', { bubbles: true }));
                        }
                    }
                }
            });
        });
    }

    /**
     * 3. Hubungkan tombol aksi cepat di toolbar
     */
    bindToolbarButtons() {
        const btnView = this.container.querySelector(this.options.selectAllViewBtn)
            || document.querySelector(this.options.selectAllViewBtn);
        if (btnView) {
            btnView.addEventListener('click', (e) => {
                e.preventDefault();
                this.selectAllView();
            });
        }

        const btnCrud = this.container.querySelector(this.options.selectAllCrudBtn)
            || document.querySelector(this.options.selectAllCrudBtn);
        if (btnCrud) {
            btnCrud.addEventListener('click', (e) => {
                e.preventDefault();
                this.selectAllCRUD();
            });
        }

        const btnUncheck = this.container.querySelector(this.options.uncheckAllBtn)
            || document.querySelector(this.options.uncheckAllBtn);
        if (btnUncheck) {
            btnUncheck.addEventListener('click', (e) => {
                e.preventDefault();
                this.uncheckAll();
            });
        }
    }

    /**
     * 4. Hubungkan tombol toggle per modul
     */
    bindModuleToggles() {
        const moduleBtns = this.container.querySelectorAll(this.options.moduleToggleBtn);

        moduleBtns.forEach((btn) => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                const modId = btn.dataset[this.options.moduleIdAttr];
                if (modId) {
                    this.toggleModule(modId);
                }
            });
        });
    }

    /**
     * Centang semua permission can_view
     */
    selectAllView() {
        const views = this.container.querySelectorAll(this.options.viewSelector);
        views.forEach((box) => {
            box.checked = true;
            box.dispatchEvent(new Event('change', { bubbles: true }));
        });
    }

    /**
     * Centang semua permission can_view dan semua aksi CRUD
     */
    selectAllCRUD() {
        this.selectAllView();

        const actions = this.container.querySelectorAll(this.options.actionSelector);
        actions.forEach((box) => {
            box.disabled = false;
            box.checked = true;
        });
    }

    /**
     * Kosongkan semua permission (uncheck semua dan disable semua aksi)
     */
    uncheckAll() {
        const allCheckboxes = this.container.querySelectorAll(this.options.checkboxSelector);
        allCheckboxes.forEach((box) => {
            box.checked = false;
        });

        const actions = this.container.querySelectorAll(this.options.actionSelector);
        actions.forEach((box) => {
            box.disabled = true;
        });
    }

    /**
     * Toggle hak akses per modul
     * @param {string|number} moduleId
     */
    toggleModule(moduleId) {
        const table = this.container.querySelector(`table[data-module-id="${moduleId}"]`);
        if (!table) return;

        const views = table.querySelectorAll(this.options.viewSelector);
        if (!views.length) return;

        const allChecked = Array.from(views).every((v) => v.checked);
        views.forEach((v) => {
            v.checked = !allChecked;
            v.dispatchEvent(new Event('change', { bubbles: true }));
        });
    }

    /**
     * Helper statis untuk inisialisasi pada container tertentu
     * @param {HTMLElement|string} container
     * @param {Object} options
     * @returns {PermissionMatrix}
     */
    static init(container = document, options = {}) {
        return new PermissionMatrix(container, options);
    }

    /**
     * Inisialisasi otomatis jika elemen matriks terdeteksi pada halaman
     */
    static autoInit() {
        const matrixExists = document.querySelector('.matrix-table, .matrix-checkbox, .perm-view');
        if (matrixExists) {
            PermissionMatrix.init(document);
        }
    }
}

// Pasang ke window agar dapat dipanggil langsung dari skrip Blade
if (typeof window !== 'undefined') {
    window.PermissionMatrix = PermissionMatrix;
    // Alias untuk kompatibilitas developer yang terbiasa dengan istilah AccessMatrix
    window.AccessMatrix = PermissionMatrix;
}

// Inisialisasi otomatis saat DOM siap
if (typeof document !== 'undefined') {
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            PermissionMatrix.autoInit();
        });
    } else {
        PermissionMatrix.autoInit();
    }
}
