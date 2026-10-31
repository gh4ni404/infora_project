# Database Testing Isolation, Schema Inspection & Tinker Safety

This rule governs all agent interactions with the local/development database and testing environments within the INFORA project.

---

## 1. Prohibition of Dummy Data Mutation in Development Database (MySQL)
1. **Strictly Forbid Running Model Factories in Tinker / CLI:**
   - **NEVER** run `Model::factory()->create()` or manual insert queries via `php artisan tinker`, temporary verification scripts, or CLI commands in the development environment (`DB_CONNECTION=mysql`).
   - Calling factories without explicit parent attributes triggers *cascading factories* (e.g., `KelasFactory` automatically triggers `SchoolFactory`), polluting master data (schools, classes, students, users) in the user's active development database.
2. **Permitted Scope of Model Factories:**
   - Model factories must only be executed inside the automated test suite (`tests/`) running on an isolated in-memory SQLite database (`:memory:`).

---

## 2. Standards for Schema & Database Structure Inspection
To inspect table structures, relationships, column types, and model attributes, **DO NOT** guess or use Tinker with state-mutating code:
1. **Migration Files (`database/migrations/`)**: The primary source of truth for column names, data types, foreign key constraints, default values, and indexes.
2. **Model Classes (`app/Models/`)**: The source of truth for Eloquent relationships (`belongsTo`, `hasMany`), `$casts`, `$fillable`, and mutators/accessors.
3. **Official Read-Only Artisan Commands**:
   - `php artisan model:show <Model>`: Inspects all attributes, data types, and relationships of a model safely without modifying data.
   - `php artisan db:table <table_name>`: Inspects physical table definitions and indexes directly.

---

## 3. Standards for UI & Business Logic Verification
1. **Automated Feature Tests are Mandatory:**
   - Verifications for web pages, CRUD forms, HTTP status responses, route redirects, and UI text must be tested using **Pest Feature Tests** (`tests/Feature/`), which automatically use `RefreshDatabase` on an in-memory SQLite database.
2. **Manual In-Browser Verification:**
   - When visual verification in the browser is needed, always use **pre-existing data** in the database (e.g., existing valid School ID 11).
   - Never create dummy schools, dummy classes, or dummy users just for demonstration/testing purposes without explicit user approval.
