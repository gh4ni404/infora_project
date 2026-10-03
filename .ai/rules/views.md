---
paths:
  - 'resources/views/**'
---

# Views

## Anonymous Blade Components
Build reusable UI components as anonymous Blade templates in resources/views/components/ using @props([...]) and invoke them with <x-...> tags.

## Design System Compliance & Strict Prohibition of Ad-Hoc Classes (detail/DESIGN.md)
Before creating or modifying any view, strictly follow the design specifications in `detail/DESIGN.md` (specifically Section 2.4):
- **Strict No Ad-Hoc Classes**: Never introduce one-off, ad-hoc, or arbitrary utility CSS classes. Never inject `<style>` tags or inline `style="..."` attributes into Blade templates.
- All visual styling must exclusively use predefined design tokens, component classes, and utilities defined in `resources/css/app.css`.

## Mandatory Component Reuse (docs/COMPONENTS.md)
Before writing manual HTML markup or wrapper structures, always consult `docs/COMPONENTS.md` and reuse existing components in `resources/views/components/`:
- Use `<x-data-table>` for data tables instead of manually writing `<div class="table-card">` or `<div class="table-toolbar">`.
- Use `<x-empty-state>` for empty states instead of writing manual `<div class="empty-state">` markup.
- Use `<x-page-header>`, `<x-stats-grid>`, `<x-stat-card>`, `<x-alert>`, `<x-status-banner>`, `<x-form-select>`, `<x-modal>`, and other system components.

## Avoid Component Overengineering
When designing or adapting Blade components, strictly maintain a lean, intuitive, and clean API:
- **No Speculative Props (KISS & YAGNI)**: Only introduce props that fulfill concrete, immediate requirements. Do not bloat components with dozens of speculative styling or format options that can be handled by standard classes or defaults.
- **Single Source of Truth / Strict No Aliases**: Never introduce duplicate or optional alias props (e.g. do NOT support both `badgeColor` and `badgeClass`, or `actionHref` and `actionUrl`). Enforce a single canonical prop name to prevent template inconsistency and unpredictable rendering outputs.
- **No Redundant Defensive Fallbacks (Fail Fast)**: Do not add redundant null-coalescing operators (`??`) or defensive fallbacks when a prop already has a default value and deterministic mapping keys. Allow invalid values or typos to fail fast during development rather than silently masking errors.

## External Form Confirmation Pattern (<x-modal name="confirm">)
When a confirmation modal (`name="confirm"` or `name="delete"`) is used to trigger an existing on-page form (rather than a self-contained modal form):
- **Use `:with-form="false"` and `form-id="..."`**: Never wrap the modal in a new `<form>` element to avoid HTTP method mismatch errors (such as `The POST method is not supported` on `PUT/PATCH` routes).
- **Link via HTML5 Form Attribute**: The modal submit button automatically binds to the target form using `form="{{ $formId }}"`.
- **No Speculative Content Props**: Use `InforaModals.bindConfirmButtons()` in JavaScript to dynamically populate the badge, title, message, and hint elements using their designated component IDs (`badgeId`, `itemTitleId`, `messageId`, `hintId`).

## Administrative Regional Cascades (InforaWilayah)
When implementing cascading administrative region selects (Provinsi -> Kabupaten -> Kecamatan -> Kelurahan), strictly use `window.InforaWilayah` (`bindCascade`, `populateCascade`, `restoreOnError`). Do not write manual `fetch` calls or duplicate dropdown event handlers in Blade views.

## Modal Template DOM Invariant
Always keep standard CRUD modal templates (`create`, `edit`, `delete`) rendered in the DOM. Do not wrap `@include` modal directives inside `@if ($user?->isSuperAdmin())`. Access control is governed by button triggers (`<x-crud-button>`) and backend middleware/policies.


