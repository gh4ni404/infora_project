# Modular Modal CRUD Architecture (Zero Standalone Edit Pages)

This rule governs all administrative module development in INFORA in accordance with `detail/DESIGN.md` Section 2.4 point 13.

---

## 1. Integrated Modal CRUD Architecture (Zero-Reload Workflow)
1. **Strict Prohibition of Standalone Create & Edit Pages:**
   - Creating standalone view files that extend `@extends('layouts.app')` for `create.blade.php` or `edit.blade.php` actions on administrative entities (School Data, Modules, Menus, Sub-Menus, Administrative Regions, Majors, etc.) is strictly prohibited.
   - All Create (`create`) and Edit (`edit`) forms must be implemented as interactive modal dialogs on top of the `index.blade.php` page.
2. **Modular Blade File Separation:**
   - Create `create.blade.php` and `edit.blade.php` files as modular Blade components that are included (`@include('...create')`, `@include('...edit')`) directly inside `index.blade.php`.
3. **Route `edit()` Compatibility:**
   - The `Controller::edit()` method must redirect the user back to the `index` route to maintain bookmark compatibility and standard RESTful resource route conventions:
     ```php
     public function edit(Entity $entity): RedirectResponse
     {
         return redirect()->route('module.entity.index');
     }
     ```

---

## 2. Dynamic Data Population & Smart Validation Auto-Reopen
1. **Edit Form Population via Data Attributes & JavaScript:**
   - The edit trigger button in data tables includes `data-*` attributes (e.g. `data-school`, `data-jurusan`, `data-module`) containing the model's attributes or JSON representation.
   - A client-side JavaScript event listener reads these attributes and instantly populates the modal form inputs before opening the modal via `InforaModals.open('modalEditXxx')`.
2. **Smart Server Validation Handling (Auto-Reopen Invariant):**
   - When server-side validation fails (`$errors->any()`), the corresponding modal dialog must automatically reopen upon page re-render while preserving old input data (`old(...)`):
     ```blade
     @if ($errors->any() && old('_method') === 'PUT')
         <script>
             document.addEventListener('DOMContentLoaded', () => {
                 InforaModals.open('modalEdit');
             });
         </script>
     @elseif ($errors->any())
         <script>
             document.addEventListener('DOMContentLoaded', () => {
                 InforaModals.open('modalCreate');
             });
         </script>
     @endif
     ```

---

## 3. Flexbox Modal Dialog Structure & Zero Clipping
1. **Sticky Header & Sticky Footer:**
   - All modals must use the structure `.modal-dialog form { display: flex; flex-direction: column; flex: 1; min-height: 0; }` with `.modal-header` and `.modal-footer` protected by `flex-shrink: 0`.
   - Submit and cancel action buttons must never be clipped (*zero clipping*) on any viewport resolution.
2. **Dynamic Scrollbar:**
   - The `.modal-body` container must utilize vertical overflow with a custom slim scrollbar (6px) and input margin normalization (`.modal-body .form-group { margin-bottom: 0; }`).

---

## 4. Decoupling CRUD Modal Templates from `isSuperAdmin` & Modal Header Invariant
1. **CRUD Modal Templates Must Always Be Available in the DOM:**
   - Standard CRUD modal template files (`create.blade.php`, `edit.blade.php`, `delete.blade.php`) **MUST NOT** be wrapped in permission directives like `@if ($user?->isSuperAdmin())` when included in `index.blade.php`.
   - Modals must unconditionally exist in the DOM so that client-side JavaScript lookups (`document.getElementById`, form reset, event binding) never fail or encounter `null` references.
2. **Layered Access Control (Do Not Hide Modal Markup):**
   - Action trigger button visibility is controlled by `<x-crud-button>` or fine-grained UI permission checks.
   - Authoritative mutation protection is strictly enforced on the backend via middleware (`SuperAdminMiddleware`, `RoleMiddleware`), Form Requests, and Model Policies.
   - Exception: Only platform-level root maintenance operations (such as global bulk synchronization of administrator accounts or full system wipes) may conditionally gate the modal inclusion itself with `@if ($user?->isSuperAdmin())`.
3. **Flexible Modal Header Rendering:**
   - The `<x-modal>` component automatically hides the `.modal-header` container when `$title` and `$subtitle` are null or empty (`@if ($title || $subtitle)`). Leverage this for centered confirmation dialogs or custom card modals without leaving an unwanted empty header bar or top divider border.
