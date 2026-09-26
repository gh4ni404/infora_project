# Pest Feature Testing & Global RefreshDatabase Invariant

Aturan ini mengikat penulisan pengujian otomatis di direktori `tests/` dalam proyek INFORA.

---

## 1. Standar Sintaks Pest PHP
1. **Gunakan Sintaks Pest Murni:**
   - Gunakan `test('deskripsi skenario pengujian', function () { ... })` alih-alih method test PHPUnit berbasis class (`public function test_...`).
   - Gunakan hook `beforeEach(function () { ... })` untuk setup user login, data sekolah, dan konteks awal.
   - Gunakan asersi ekspresif Pest `expect(...)` dan asersi response HTTP Laravel (`assertOk()`, `assertRedirect()`, `assertForbidden()`, `assertSessionHasNoErrors()`).

---

## 2. Invariant `RefreshDatabase` Global
1. **Jangan Menduplikasi `RefreshDatabase` di File Feature Test:**
   - Trait `Illuminate\Foundation\Testing\RefreshDatabase` telah didaftarkan secara global untuk seluruh test di direktori `tests/Feature/` pada file `tests/Pest.php`:
     ```php
     pest()->extend(TestCase::class)
         ->use(RefreshDatabase::class)
         ->in('Feature');
     ```
   - **Dilarang keras** menambahkan deklarasi `uses(RefreshDatabase::class);` atau `use RefreshDatabase;` di dalam file-file feature test individu. Redundansi ini tidak diperlukan dan berisiko memicu overhead inisialisasi ganda.

---

## 3. Data Test & Model Factories
1. **Gunakan Factory dengan Custom States:**
   - Manfaatkan model factory bawaan ([SchoolFactory](file:///home/gania/infora_project/database/factories/SchoolFactory.php), [UserFactory](file:///home/gania/infora_project/database/factories/UserFactory.php), [JurusanFactory](file:///home/gania/infora_project/database/factories/JurusanFactory.php)) untuk membuat fixture:
     ```php
     $school = School::factory()->smk()->create();
     $admin = User::factory()->create(['user_type' => 'super_admin']);
     ```
2. **Pengecekan Integritas Basis Data:**
   - Pastikan setiap operasi create, update, dan delete diuji efek sampingnya pada database menggunakan `assertDatabaseHas('table_name', [...])` dan `assertDatabaseMissing('table_name', [...])`.
3. **Eksekusi Pengujian:**
   - Jalankan pengujian selalu menggunakan helper `./dev test` atau `.\dev.ps1 test`.
