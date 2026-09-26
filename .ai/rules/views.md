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
- Use `<x-page-header>`, `<x-stats-grid>`, `<x-stat-card>`, `<x-alert>`, `<x-form-select>`, `<x-modal>`, and other system components.

## Avoid Component Overengineering
When adapting existing components for greater flexibility (e.g. supporting standalone contexts outside tables), make minimal, non-breaking adjustments. Do not bloat components with unnecessary props or speculative logic; keep component APIs simple, clean, and intuitive.
