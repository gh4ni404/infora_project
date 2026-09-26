<?php

use App\Support\ImageUploader;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

uses(TestCase::class);

beforeEach(function () {
    Storage::fake('public');
});

test('image uploader generates filename according to design specification standard', function () {
    // Format: {modul}_u{user_id}_{YYYYMMDD_His}_{random_8char}.{ekstensi}
    $filename = ImageUploader::generateFilename('kbm', 'webp', 7);

    // Contoh: kbm_u7_20260905_143022_a7b9c1d2.webp
    expect($filename)->toMatch('/^kbm_u7_\d{8}_\d{6}_[a-z0-9]{8}\.webp$/');
});

test('image uploader generates filename with fallback module and auth user', function () {
    $filename = ImageUploader::generateFilename('', 'png');

    expect($filename)->toMatch('/^img_u0_\d{8}_\d{6}_[a-z0-9]{8}\.png$/');
});

test('image uploader uploads valid base64 png successfully', function () {
    // 1x1 transparent PNG in base64
    $pngBase64 = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==';

    $path = ImageUploader::uploadBase64($pngBase64, 'logos', 'sekolah', 1);

    expect($path)->not->toBeNull()
        ->and($path)->toMatch('/^logos\/sekolah_u1_\d{8}_\d{6}_[a-z0-9]{8}\.png$/');

    Storage::disk('public')->assertExists($path);
});

test('image uploader uploads valid base64 webp successfully', function () {
    // Tiny WEBP base64
    $webpBase64 = 'data:image/webp;base64,UklGRhoAAABXRUJQVlA4TA0AAAAvAAAAEAcQERGIiP4HAA==';

    $path = ImageUploader::uploadBase64($webpBase64, 'photos/kbm', 'kbm', 7);

    expect($path)->not->toBeNull()
        ->and($path)->toMatch('/^photos\/kbm\/kbm_u7_\d{8}_\d{6}_[a-z0-9]{8}\.webp$/');

    Storage::disk('public')->assertExists($path);
});

test('image uploader rejects invalid base64 string or unsupported mime type', function () {
    expect(ImageUploader::uploadBase64('invalid-string'))->toBeNull();
    expect(ImageUploader::uploadBase64('data:application/pdf;base64,dGVzdA=='))->toBeNull();
    expect(ImageUploader::uploadBase64('data:image/xyz;base64,dGVzdA=='))->toBeNull();
});

test('image uploader rejects base64 that exceeds max bytes limit', function () {
    // Dummy 100 bytes data with max limit 50 bytes
    $dummyData = base64_encode(str_repeat('A', 100));
    $base64 = "data:image/png;base64,{$dummyData}";

    $path = ImageUploader::uploadBase64($base64, 'logos', 'sekolah', 1, 50);

    expect($path)->toBeNull();
});

test('image uploader deletes existing file cleanly', function () {
    $path = 'logos/test_logo.png';
    Storage::disk('public')->put($path, 'dummy content');

    expect(Storage::disk('public')->exists($path))->toBeTrue();

    $deleted = ImageUploader::delete($path);

    expect($deleted)->toBeTrue();
    Storage::disk('public')->assertMissing($path);

    // Menghapus path kosong atau tidak ada menghasilkan false
    expect(ImageUploader::delete(null))->toBeFalse();
    expect(ImageUploader::delete(''))->toBeFalse();
    expect(ImageUploader::delete('nonexistent.png'))->toBeFalse();
});

test('image uploader extracts extension and mime type accurately', function () {
    $pngBase64 = 'data:image/png;base64,AAAA';
    $jpgBase64 = 'data:image/jpeg;base64,BBBB';
    $webpBase64 = 'data:image/webp;base64,CCCC';

    expect(ImageUploader::extractExtension($pngBase64))->toBe('png')
        ->and(ImageUploader::extractExtension($jpgBase64))->toBe('jpg')
        ->and(ImageUploader::extractExtension($webpBase64))->toBe('webp')
        ->and(ImageUploader::extractExtension('invalid'))->toBeNull();

    expect(ImageUploader::extractMimeType($pngBase64))->toBe('image/png')
        ->and(ImageUploader::extractMimeType($jpgBase64))->toBe('image/jpeg')
        ->and(ImageUploader::extractMimeType('invalid'))->toBeNull();
});
