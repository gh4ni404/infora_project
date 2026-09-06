<?php

use App\Models\Menu;
use App\Models\Module;
use App\Models\User;
use App\Services\DatabaseBackupService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;

beforeEach(function () {
    $this->superAdmin = User::factory()->create([
        'username' => 'superadmin_backup',
        'email' => 'superadmin_backup@infora.test',
        'user_type' => 'super_admin',
        'is_active' => true,
    ]);

    $this->teacherUser = User::factory()->create([
        'username' => 'guru_backup',
        'email' => 'guru_backup@infora.test',
        'user_type' => 'guru',
        'is_active' => true,
    ]);

    $this->backupService = app(DatabaseBackupService::class);
    $this->backupDir = $this->backupService->getBackupDirectory();
});

afterEach(function () {
    // Clean up temporary test files in backup directory
    $testFiles = File::glob($this->backupDir.DIRECTORY_SEPARATOR.'test_*.sql');
    foreach ($testFiles as $file) {
        File::delete($file);
    }
});

test('guest cannot access backup-restore page and is redirected to login', function () {
    $response = $this->get('/backup-restore');

    $response->assertRedirect('/login');
});

test('non super admin cannot access backup-restore page', function () {
    $response = $this->actingAs($this->teacherUser)->get('/backup-restore');

    // Forbidden for non super_admin
    $response->assertForbidden();
});

test('super admin can view backup-restore page with database stats', function () {
    $response = $this->actingAs($this->superAdmin)->get('/backup-restore');

    $response->assertOk();
    $response->assertSee('Cadangan & Pemulihan Basis Data', false);
    $response->assertSee('Basis Data Aktif');
    $response->assertSee('Total Tabel Sistem');
    $response->assertSee('Buat Cadangan Baru');
});

test('backup-restore menu is marked active when on /backup-restore route', function () {
    $module = Module::firstOrCreate(['name' => 'PENGATURAN SISTEM'], ['order' => 1, 'is_active' => true]);
    $menu = Menu::firstOrCreate(
        ['route_name' => 'backup-restore'],
        [
            'module_id' => $module->id,
            'name' => 'Backup & Restore',
            'icon' => 'folder',
            'order' => 1,
            'is_active' => true,
        ]
    );

    expect($menu->route_url)->toBe(route('backup-restore'));

    $this->actingAs($this->superAdmin)->get('/backup-restore');

    expect($menu->isRouteActive())->toBeTrue();
});

test('super admin can create a new database backup dump', function () {
    $response = $this->actingAs($this->superAdmin)->post(route('backup-restore.create'));

    $response->assertRedirect(route('backup-restore'));
    $response->assertSessionHas('success');

    $backups = $this->backupService->listBackups();
    expect($backups->isNotEmpty())->toBeTrue();

    $latest = $backups->first();
    expect($latest['size_bytes'])->toBeGreaterThan(0)
        ->and(File::exists($latest['path']))->toBeTrue();

    // Verify SQL dump contains table structure and data
    $content = File::get($latest['path']);
    $expectedForeignCheck = DB::connection()->getDriverName() === 'sqlite'
        ? 'PRAGMA foreign_keys = OFF;'
        : 'SET FOREIGN_KEY_CHECKS=0;';

    expect($content)->toContain($expectedForeignCheck)
        ->and($content)->toContain('CREATE TABLE');

    // Clean up created file
    File::delete($latest['path']);
});

test('super admin can download an existing backup file', function () {
    $dummyFilename = 'test_backup_download.sql';
    $dummyPath = $this->backupDir.DIRECTORY_SEPARATOR.$dummyFilename;
    File::put($dummyPath, '-- Dummy SQL Backup Content');

    $response = $this->actingAs($this->superAdmin)->get(route('backup-restore.download', $dummyFilename));

    $response->assertOk();
    $response->assertHeader('content-disposition', 'attachment; filename='.$dummyFilename);
});

test('downloading non-existent or invalid filename fails gracefully', function () {
    $responseNonExistent = $this->actingAs($this->superAdmin)->get(route('backup-restore.download', 'non_existent.sql'));
    $responseNonExistent->assertRedirect(route('backup-restore'));
    $responseNonExistent->assertSessionHas('error');

    // Invalid extension or traversal attempt
    $responseInvalid = $this->actingAs($this->superAdmin)->get(route('backup-restore.download', 'invalid_script.php'));
    $responseInvalid->assertRedirect(route('backup-restore'));
    $responseInvalid->assertSessionHas('error');
});

test('super admin can delete an existing backup file', function () {
    $dummyFilename = 'test_backup_delete.sql';
    $dummyPath = $this->backupDir.DIRECTORY_SEPARATOR.$dummyFilename;
    File::put($dummyPath, '-- Dummy SQL to Delete');

    expect(File::exists($dummyPath))->toBeTrue();

    $response = $this->actingAs($this->superAdmin)->delete(route('backup-restore.destroy', $dummyFilename));

    $response->assertRedirect(route('backup-restore'));
    $response->assertSessionHas('success');
    expect(File::exists($dummyPath))->toBeFalse();
});

test('super admin can restore database from an existing sql file', function () {
    $dummyFilename = 'test_backup_restore.sql';
    $dummyPath = $this->backupDir.DIRECTORY_SEPARATOR.$dummyFilename;
    File::put($dummyPath, "SELECT 1;\n");

    $response = $this->actingAs($this->superAdmin)->post(route('backup-restore.restore'), [
        'filename' => $dummyFilename,
    ]);

    $response->assertRedirect(route('backup-restore'));
    $response->assertSessionHas('success');
});

test('super admin can restore database from an uploaded sql file', function () {
    $uploadedFile = UploadedFile::fake()->createWithContent('uploaded_backup.sql', "SELECT 1;\n");

    $response = $this->actingAs($this->superAdmin)->post(route('backup-restore.restore'), [
        'backup_file' => $uploadedFile,
    ]);

    $response->assertRedirect(route('backup-restore'));
    $response->assertSessionHas('success');
});

test('restore request fails if neither filename nor backup_file is supplied', function () {
    $response = $this->actingAs($this->superAdmin)->post(route('backup-restore.restore'), []);

    $response->assertSessionHasErrors(['backup_source']);
});

test('restore request fails if uploaded file is not sql or txt', function () {
    $invalidFile = UploadedFile::fake()->create('malicious.php', 100);

    $response = $this->actingAs($this->superAdmin)->post(route('backup-restore.restore'), [
        'backup_file' => $invalidFile,
    ]);

    $response->assertSessionHasErrors(['backup_file']);
});
