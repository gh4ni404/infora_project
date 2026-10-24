# Standard Anonymous Blade Components Ecosystem (25 Components)

This rule governs the creation and modification of all user interfaces (Blade views) across the INFORA project in accordance with official specifications in `docs/COMPONENTS.md`.

---

## 1. Mandatory Usage of Official Blade Components
1. **Strict Prohibition of Raw HTML Form & UI Markup:**
   - Writing repetitive manual boilerplate structures like `<div class="form-group"><label>...<input class="form-control">...` is strictly prohibited.
   - You must exclusively utilize the **25 Anonymous Blade Components** provided in `resources/views/components/`.
2. **Catalog of 25 Official Components:**
   - **Form & Input:**
     - `<x-form-input>` (Text, number, email, password, date, and hidden inputs with `old()` binding, required asterisk badge, and automatic error handling).
     - `<x-form-select>` (Standard select dropdown with collection options and dynamic attributes).
     - `<x-form-textarea>` (Textarea with auto-sizing and error handling).
     - `<x-form-checkbox>` (Touch-friendly checkbox toggle/switch).
     - `<x-searchable-select>` (Searchable dropdown with fast client-side query filtering).
     - `<x-icon-picker>` (Visual dialog picker for interactive Lucide icons).
     - `<x-image-upload>` (Modern image upload integrated with live base64 preview and `ImageUploader` service).
     - `<x-file-upload>` (Universal drag-and-drop file upload with preview, file icon, format validation, and clear button).
   - **Page Header, Navigation & Metrics:**
     - `<x-page-header>` (Page title, breadcrumbs, subtitle, action buttons).
     - `<x-tab-pills>` & `<x-tab-pill>` (Interactive pill-based tab switcher).
     - `<x-stats-grid>` & `<x-stat-card>` (Statistical metric cards with trend indicators and color accents).
     - `<x-alert>` (Contextual alert notifications with Lucide icons).
     - `<x-status-banner>` (Persistent contextual alert banner with Lucide icons and rich slots).
   - **Data Presentation & Tables:**
     - `<x-data-table>` (Responsive data table styled to INFORA design tokens).
     - `<x-search-toolbar>` (Fast search and filtering toolbar).
     - `<x-table-actions>` (Table row actions: edit modal, status toggle, delete modal).
     - `<x-crud-button>` (Unified RBAC authorized action buttons: create, edit, delete, import, export, view, sync).
     - `<x-empty-state>` (Polished empty state display for zero records or search misses).
     - `<x-pagination-summary>` & `<x-pagination>` (Adaptive pagination and result range summary).
   - **Dialogs & Modals:**
     - `<x-modal>` (Standardized flexbox modal with auto-scroll and zero clipping).
     - `<x-progress-modal>` (Multi-stage smooth loading progress modal).
   - **Graphics & Icons:**
     - `<x-icon>` (Native Lucide SVG open-source icon renderer).

---

## 2. Integration with Companion JavaScript Modules
Blade UI components operate seamlessly alongside 6 native client-side controller modules:
1. `window.InforaModals` → Controls modal dialog lifecycle (`InforaModals.open(id)`, `InforaModals.close(id)`, `InforaModals.bindConfirmButtons()`).
2. `window.SearchableSelect` → Dropdown initialization with live query search and keyboard navigation.
3. `window.ImageUpload` → Client-side FileReader base64 handler and instant image previewing.
4. `window.InforaProgress` → Global loading screen and multi-stage progress bar management.
5. `window.PermissionMatrix` → Dynamic RBAC permissions matrix and bulk selection handler.
6. `window.InforaWilayah` → Cascading administrative regional data management (Provinsi ➔ Kabupaten ➔ Kecamatan ➔ Kelurahan) with in-memory caching, edit form auto-population, and server validation recovery (`restoreOnError`).
