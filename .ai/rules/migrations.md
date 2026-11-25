---
paths:
  - 'database/migrations/**'
---

# Migrations

## Foreign Keys and Reversible Migrations
Define foreign keys using $table->foreignId("column_id")->constrained("table_name") with explicit cascade or null onDelete behaviors. Always implement a reversible down(): void method.

## Zero `unique` Constraints Policy
Do NOT add DBMS `->unique()` constraints on text, code, slug, setting key, or composite business/policy columns (rely on Primary Key `id`, Foreign Keys, plain `->index()`, and `updateOrCreate()` or transactional delete-and-insert at the application layer). See `.agents/rules/avoid-unique-constraints-on-entity-attributes.md`.

## MariaDB-First Cross-Engine Compatibility
Target MariaDB (10.6+ LTS / minimum 10.3) with `utf8mb4_unicode_ci` as the primary production database while maintaining compatibility with MySQL 8.0 and SQLite `:memory:` (used by Pest `RefreshDatabase`):
- Use Laravel Schema Builder types (`json()`, `enum()`, `index()`) and standard SQL functions (`REPLACE(...)`) instead of vendor-proprietary DDL or MySQL 8-only collations (`utf8mb4_0900_ai_ci`) or operators.
- Guard any engine-specific raw statements (`PRAGMA ...` vs `SET FOREIGN_KEY_CHECKS=...`) by inspecting the active driver name.
- Keep indexed string columns reasonably bounded (e.g., 20–150 chars on codes/keys in composite indexes) so `utf8mb4` index byte sizes stay well within InnoDB limits.

## Idempotent DDL Standard
Prevent backup restore collisions (`Table already exists` / `Duplicate column`) by making every migration defensive and idempotent:
- **New Tables:** Guard `Schema::create` by returning early if the table already exists:
  ```php
  if (Schema::hasTable('table_name')) {
      return;
  }
  ```
- **New Columns:** Guard column additions inside `Schema::table`:
  ```php
  if (! Schema::hasColumn('table_name', 'column_name')) {
      $table->string('column_name')->nullable();
  }
  ```

