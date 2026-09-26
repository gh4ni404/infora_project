<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImageUploader
{
    /**
     * Ukuran maksimal berkas bawaan (1MB dalam bytes).
     */
    public const DEFAULT_MAX_BYTES = 1048576; // 1MB

    /**
     * Pemetaan MIME type ke ekstensi berkas gambar yang didukung.
     *
     * @var array<string, string>
     */
    protected static array $mimeExtensionMap = [
        'png' => 'png',
        'jpeg' => 'jpg',
        'jpg' => 'jpg',
        'webp' => 'webp',
        'gif' => 'gif',
        'svg+xml' => 'svg',
    ];

    /**
     * Memproses data Base64 gambar dan menyimpannya ke storage sesuai standar penamaan INFORA:
     * {modul}_u{user_id}_{YYYYMMDD_His}_{random_8char}.{ekstensi}
     *
     * Contoh: kbm_u7_20260905_143022_a7b9c1d2.webp
     *
     * @param  string  $base64String  String Data URI Base64 (data:image/...;base64,...)
     * @param  string  $directory  Direktori penyimpanan relatif (contoh: 'logos', 'photos/siswa')
     * @param  string  $module  Pengenal modul (contoh: 'sekolah', 'kbm', 'siswa', 'guru')
     * @param  int|null  $userId  ID pengguna pengunggah (opsional, bawaan: auth()->id() ?? 0)
     * @param  int  $maxBytes  Batas ukuran biner gambar dalam bytes (bawaan: 1MB)
     * @param  string  $disk  Nama disk storage Laravel (bawaan: 'public')
     * @return string|null Path berkas relatif yang tersimpan, atau null jika gagal/tidak valid
     */
    public static function uploadBase64(
        string $base64String,
        string $directory = 'photos',
        string $module = 'img',
        ?int $userId = null,
        int $maxBytes = self::DEFAULT_MAX_BYTES,
        string $disk = 'public'
    ): ?string {
        if (! preg_match('/^data:image\/([a-zA-Z0-9\+\-]+);base64,/', $base64String, $matches)) {
            return null;
        }

        $rawMimeSubtype = strtolower($matches[1]);
        $extension = self::$mimeExtensionMap[$rawMimeSubtype] ?? null;

        if ($extension === null) {
            return null;
        }

        $payload = preg_replace('/^data:image\/[a-zA-Z0-9\+\-]+;base64,/', '', $base64String);
        $imageData = base64_decode($payload, true);

        if ($imageData === false || strlen($imageData) === 0) {
            return null;
        }

        // Validasi batasan ukuran berkas
        if (strlen($imageData) > $maxBytes) {
            return null;
        }

        $filename = self::generateFilename($module, $extension, $userId);
        $cleanDirectory = trim($directory, '/');
        $fullPath = $cleanDirectory !== '' ? "{$cleanDirectory}/{$filename}" : $filename;

        Storage::disk($disk)->put($fullPath, $imageData);

        return $fullPath;
    }

    /**
     * Menghasilkan nama berkas semantik standar INFORA:
     * {modul}_u{user_id}_{YYYYMMDD_His}_{random_8char}.{ekstensi}
     *
     * @param  string  $module  Nama modul (contoh: 'kbm', 'sekolah', 'siswa')
     * @param  string  $extension  Ekstensi berkas (contoh: 'webp', 'png', 'jpg')
     * @param  int|null  $userId  ID pengguna (bawaan: auth()->id() ?? 0)
     */
    public static function generateFilename(string $module, string $extension, ?int $userId = null): string
    {
        $cleanModule = Str::slug($module, '_');
        if ($cleanModule === '') {
            $cleanModule = 'img';
        }

        $uid = $userId ?? auth()->id() ?? 0;
        $timestamp = now()->format('Ymd_His');
        $randomHash = Str::lower(Str::random(8));
        $cleanExt = ltrim(strtolower($extension), '.');

        return "{$cleanModule}_u{$uid}_{$timestamp}_{$randomHash}.{$cleanExt}";
    }

    /**
     * Menghapus berkas dari storage jika berkas tersebut ada.
     *
     * @param  string|null  $path  Path berkas relatif dalam storage
     * @param  string  $disk  Nama disk storage Laravel (bawaan: 'public')
     */
    public static function delete(?string $path, string $disk = 'public'): bool
    {
        if (blank($path)) {
            return false;
        }

        if (Storage::disk($disk)->exists($path)) {
            return Storage::disk($disk)->delete($path);
        }

        return false;
    }

    /**
     * Mengekstrak ekstensi berkas dari string Data URI Base64.
     */
    public static function extractExtension(string $base64String): ?string
    {
        if (preg_match('/^data:image\/([a-zA-Z0-9\+\-]+);base64,/', $base64String, $matches)) {
            $mime = strtolower($matches[1]);

            return self::$mimeExtensionMap[$mime] ?? null;
        }

        return null;
    }

    /**
     * Mengekstrak MIME type lengkap dari string Data URI Base64.
     */
    public static function extractMimeType(string $base64String): ?string
    {
        if (preg_match('/^data:(image\/[a-zA-Z0-9\+\-]+);base64,/', $base64String, $matches)) {
            return strtolower($matches[1]);
        }

        return null;
    }
}
