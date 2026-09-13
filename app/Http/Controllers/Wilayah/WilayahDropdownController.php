<?php

namespace App\Http\Controllers\Wilayah;

use App\Http\Controllers\Controller;
use App\Models\Kabupaten;
use App\Models\Kecamatan;
use App\Models\Kelurahan;
use App\Models\Provinsi;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WilayahDropdownController extends Controller
{
    /**
     * Mengambil daftar provinsi aktif untuk opsi dropdown.
     */
    public function provinsi(): JsonResponse
    {
        $provinsi = Provinsi::query()
            ->aktif()
            ->orderBy('kode')
            ->get(['id', 'kode', 'nama']);

        return response()->json($provinsi);
    }

    /**
     * Mengambil daftar kabupaten/kota aktif berdasarkan provinsi_id untuk opsi dropdown.
     */
    public function kabupaten(Request $request): JsonResponse
    {
        $provinsiId = $request->query('provinsi_id');
        if (! $provinsiId) {
            return response()->json([]);
        }

        $kabupaten = Kabupaten::query()
            ->aktif()
            ->where('provinsi_id', (int) $provinsiId)
            ->orderBy('kode')
            ->get(['id', 'provinsi_id', 'kode', 'tipe', 'nama']);

        return response()->json($kabupaten);
    }

    /**
     * Mengambil daftar kecamatan aktif berdasarkan kabupaten_id untuk opsi dropdown.
     */
    public function kecamatan(Request $request): JsonResponse
    {
        $kabupatenId = $request->query('kabupaten_id');
        if (! $kabupatenId) {
            return response()->json([]);
        }

        $kecamatan = Kecamatan::query()
            ->aktif()
            ->where('kabupaten_id', (int) $kabupatenId)
            ->orderBy('kode')
            ->get(['id', 'kabupaten_id', 'kode', 'nama']);

        return response()->json($kecamatan);
    }

    /**
     * Mengambil daftar kelurahan/desa aktif berdasarkan kecamatan_id untuk opsi dropdown.
     */
    public function kelurahan(Request $request): JsonResponse
    {
        $kecamatanId = $request->query('kecamatan_id');
        if (! $kecamatanId) {
            return response()->json([]);
        }

        $kelurahan = Kelurahan::query()
            ->aktif()
            ->where('kecamatan_id', (int) $kecamatanId)
            ->orderBy('kode')
            ->get(['id', 'kecamatan_id', 'kode', 'tipe', 'nama', 'kode_pos']);

        return response()->json($kelurahan);
    }
}
