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
    $this->publicStorageDir = storage_path('app/public');
});

afterEach(function () {
    // Clean up temporary test files in backup directory
    $testFiles = array_merge(
        File::glob($this->backupDir.DIRECTORY_SEPARATOR.'test_*.sql') ?: [],
        File::glob($this->backupDir.DIRECTORY_SEPARATOR.'test_*.zip') ?: []
    );
    foreach ($testFiles as $file) {
        File::delete($file);
    }

    // Clean up dummy test assets in public storage
    if (File::exists($this->publicStorageDir.DIRECTORY_SEPARATOR.'test_asset.txt')) {
        File::delete($this->publicStorageDir.DIRECTORY_SEPARATOR.'test_asset.txt');
    }
});

test('guest cannot access backup-restore page and is redirected to login', function () {
    $response = $this->get('/backup-restore');

    $response->assertRedirect('/login');
});

test('non super admin cannot access backup-restore page', function () {
    $response = $this->actingAs($this->teacherUser)->get('/backup-restore');

    $response->assertForbidden();
});

test('super admin can view backup-restore page with database and storage stats', function () {
    $response = $this->actingAs($this->superAdmin)->get('/backup-restore');

    $response->assertOk();
    $response->assertSee('Cadangan & Pemulihan Sistem', false);
    $response->assertSee('Basis Data Aktif');
    $response->assertSee('Total Tabel Sistem');
    $response->assertSee('Aset Storage Pengguna');
    $response->assertSee('Buat Cadangan Lengkap (ZIP)');
    $response->assertSee('Cadangan Database (SQL)');
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

test('super admin can create a new full system backup zip archive', function () {
    // Create a dummy file in public storage to ensure storage archiving is covered
    File::ensureDirectoryExists($this->publicStorageDir);
    File::put($this->publicStorageDir.DIRECTORY_SEPARATOR.'test_asset.txt', 'Asset content for backup');

    $response = $this->actingAs($this->superAdmin)->post(route('backup-restore.create'), [
        'type' => 'full',
    ]);

    $response->assertRedirect(route('backup-restore'));
    $response->assertSessionHas('success');

    $backups = $this->backupService->listBackups();
    $latest = $backups->first();

    expect($latest['type'])->toBe('full')
        ->and(str_ends_with($latest['filename'], '.zip'))->toBeTrue()
        ->and(File::exists($latest['path']))->toBeTrue();

    // Verify ZIP contains manifest.json, database.sql, and storage files
    $zip = new ZipArchive;
    expect($zip->open($latest['path']))->toBeTrue();
    expect($zip->locateName('manifest.json'))->not()->toBeFalse()
        ->and($zip->locateName('database.sql'))->not()->toBeFalse()
        ->and($zip->locateName('storage/test_asset.txt'))->not()->toBeFalse();

    $manifestContent = $zip->getFromName('manifest.json');
    $manifestData = json_decode($manifestContent, true);
    expect($manifestData['backup_type'])->toBe('full')
        ->and($manifestData['database'])->toHaveKey('total_tables');

    $zip->close();

    // Clean up created file
    File::delete($latest['path']);
});

test('super admin can create a new database-only backup dump', function () {
    $response = $this->actingAs($this->superAdmin)->post(route('backup-restore.create'), [
        'type' => 'database',
    ]);

    $response->assertRedirect(route('backup-restore'));
    $response->assertSessionHas('success');

    $backups = $this->backupService->listBackups();
    $latest = $backups->first();

    expect($latest['type'])->toBe('database')
        ->and(str_ends_with($latest['filename'], '.sql'))->toBeTrue()
        ->and($latest['size_bytes'])->toBeGreaterThan(0);

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

    $responseInvalid = $this->actingAs($this->superAdmin)->get(route('backup-restore.download', 'invalid_script.php'));
    $responseInvalid->assertRedirect(route('backup-restore'));
    $responseInvalid->assertSessionHas('error');
});

test('super admin can delete an existing backup file', function () {
    $dummyFilename = 'test_backup_delete.zip';
    $dummyPath = $this->backupDir.DIRECTORY_SEPARATOR.$dummyFilename;
    File::put($dummyPath, 'Dummy ZIP Content');

    expect(File::exists($dummyPath))->toBeTrue();

    $response = $this->actingAs($this->superAdmin)->delete(route('backup-restore.destroy', $dummyFilename));

    $response->assertRedirect(route('backup-restore'));
    $response->assertSessionHas('success');
    expect(File::exists($dummyPath))->toBeFalse();
});

test('super admin can restore full system from an existing zip archive and auto-links storage', function () {
    $zipFilename = 'test_full_restore.zip';
    $zipPath = $this->backupDir.DIRECTORY_SEPARATOR.$zipFilename;

    // Create a real mock zip with database.sql and a storage asset
    $zip = new ZipArchive;
    $zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);
    $zip->addFromString('database.sql', "SELECT 1;\n");
    $zip->addFromString('manifest.json', json_encode(['backup_type' => 'full']));
    $zip->addFromString('storage/restored_image.txt', 'Restored image content');
    $zip->close();

    $response = $this->actingAs($this->superAdmin)->post(route('backup-restore.restore'), [
        'filename' => $zipFilename,
    ]);

    $response->assertRedirect(route('backup-restore'));
    $response->assertSessionHas('success');

    // Verify storage file was extracted to storage/app/public
    expect(File::exists($this->publicStorageDir.DIRECTORY_SEPARATOR.'restored_image.txt'))->toBeTrue()
        ->and(File::get($this->publicStorageDir.DIRECTORY_SEPARATOR.'restored_image.txt'))->toBe('Restored image content');

    // Clean up
    File::delete($this->publicStorageDir.DIRECTORY_SEPARATOR.'restored_image.txt');
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

test('super admin can restore database from an uploaded zip or sql file', function () {
    $uploadedFile = UploadedFile::fake()->createWithContent('uploaded_backup.sql', "SELECT 1;\n");

    $response = $this->actingAs($this->superAdmin)->post(route('backup-restore.restore'), [
        'backup_file' => $uploadedFile,
    ]);

    $response->assertRedirect(route('backup-restore'));
    $response->assertSessionHas('success');
});

test('ensureStorageLink checks symlink health and avoids redundant creation if already valid', function () {
    $link = public_path('storage');
    $target = storage_path('app/public');
    File::ensureDirectoryExists($target);

    // Call ensureStorageLink
    $result = $this->backupService->ensureStorageLink();
    expect($result)->toBeTrue();

    // Calling again should return true directly without issues
    $secondResult = $this->backupService->ensureStorageLink();
    expect($secondResult)->toBeTrue();
});

test('restore request fails if neither filename nor backup_file is supplied', function () {
    $response = $this->actingAs($this->superAdmin)->post(route('backup-restore.restore'), []);

    $response->assertSessionHasErrors(['backup_source']);
});

test('restore request fails if uploaded file is not zip, sql, or txt', function () {
    $invalidFile = UploadedFile::fake()->create('malicious.php', 100);

    $response = $this->actingAs($this->superAdmin)->post(route('backup-restore.restore'), [
        'backup_file' => $invalidFile,
    ]);

    $response->assertSessionHasErrors(['backup_file']);
});

test('super admin can stream backup creation with real-time SSE progress events', function () {
    $response = $this->actingAs($this->superAdmin)->post(
        route('backup-restore.create'),
        ['type' => 'database'],
        ['Accept' => 'text/event-stream']
    );

    $response->assertOk();
    expect($response->headers->get('Content-Type'))->toContain('text/event-stream');

    $content = $response->streamedContent();
    expect($content)->toContain('data: {')
        ->toContain('"stage":"Ekspor Basis Data"')
        ->toContain('"status":"completed"')
        ->toContain('"percent":100');
});

test('super admin can stream database restoration with real-time SSE progress events', function () {
    $dummyFilename = 'test_stream_restore.sql';
    $dummyPath = $this->backupDir.DIRECTORY_SEPARATOR.$dummyFilename;
    File::put($dummyPath, "SELECT 1;\n");

    $response = $this->actingAs($this->superAdmin)->post(
        route('backup-restore.restore'),
        ['filename' => $dummyFilename],
        ['Accept' => 'text/event-stream']
    );

    $response->assertOk();
    expect($response->headers->get('Content-Type'))->toContain('text/event-stream');

    $content = $response->streamedContent();
    expect($content)->toContain('data: {')
        ->toContain('"stage":"Pemulihan Basis Data"')
        ->toContain('"status":"completed"')
        ->toContain('"percent":100');
});

test('backup-restore page contains real-time progress bar, stepper, and zero inline styles', function () {
    $response = $this->actingAs($this->superAdmin)->get('/backup-restore');

    $response->assertOk();
    $content = $response->getContent();

    // Verify progress elements
    $response->assertSee('id="modalSystemProgress"', false);
    $response->assertSee('id="systemProgressBar"', false);
    $response->assertSee('class="infora-progress"', false);
    $response->assertSee('id="systemProgressPercent"', false);
    $response->assertSee('id="systemProgressStepper"', false);
    $response->assertSee('id="systemProgressStage"', false);
    $response->assertSee('id="systemProgressDetail"', false);

    // Verify ZERO inline style="..." attributes in HTML elements
    expect(preg_match('/<[a-z0-9\-]+[^>]*\sstyle=["\'][^"\']*["\']/i', $content))->toBe(0);
});
