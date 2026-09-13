<?php

namespace App\Http\Controllers\Wilayah;

use App\Http\Controllers\Controller;
use App\Http\Requests\Wilayah\StoreKelurahanRequest;
use App\Http\Requests\Wilayah\UpdateKelurahanRequest;
use App\Models\Kabupaten;
use App\Models\Kecamatan;
use App\Models\Kelurahan;
use App\Models\Provinsi;
use App\Models\School;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KelurahanController extends Controller
{
    /**
     * Menampilkan daftar wilayah kelurahan & desa dengan filter berjenjang, tipe, status, pencarian, dan paginasi.
     */
    public function index(Request $request): View
    {
        $query = Kelurahan::query()
            ->with(['kecamatan.kabupaten.provinsi'])
            ->orderBy('kode')
            ->orderBy('nama');

        // Filter pencarian universal (kode, nama, kode pos, kecamatan, kabupaten, provinsi)
        if ($request->filled('search')) {
            $query->search($request->query('search'));
        }

        // Filter berdasarkan provinsi induk
        if ($request->filled('provinsi_id')) {
            $query->filterByProvinsi((int) $request->query('provinsi_id'));
        }

        // Filter berdasarkan kabupaten induk
        if ($request->filled('kabupaten_id')) {
            $query->filterByKabupaten((int) $request->query('kabupaten_id'));
        }

        // Filter berdasarkan kecamatan induk
        if ($request->filled('kecamatan_id')) {
            $query->filterByKecamatan((int) $request->query('kecamatan_id'));
        }

        // Filter berdasarkan tipe (Kelurahan / Desa)
        if ($request->filled('tipe')) {
            $query->filterByTipe($request->query('tipe'));
        }

        // Filter status aktif/nonaktif
        if ($request->filled('status')) {
            $statusValue = $request->query('status');
            if ($statusValue === '1' || $statusValue === 'aktif') {
                $query->where('status', true);
            } elseif ($statusValue === '0' || $statusValue === 'nonaktif') {
                $query->where('status', false);
            }
        }

        // Opsi batas data per halaman (15, 30, 90, semua)
        $perPageInput = $request->query('per_page', '15');
        if ($perPageInput === 'semua' || $perPageInput === 'all') {
            $perPage = 1000;
        } elseif (in_array($perPageInput, ['15', '30', '90'], true)) {
            $perPage = (int) $perPageInput;
        } else {
            $perPage = 15;
            $perPageInput = '15';
        }

        $kelurahanList = $query->paginate($perPage)->withQueryString();

        // Data referensi provinsi untuk dropdown filter dan cascading modal
        $provinsiList = Provinsi::query()
            ->aktif()
            ->orderBy('kode')
            ->get(['id', 'kode', 'nama']);

        // Data referensi kabupaten untuk cascading dropdown
        $kabupatenList = Kabupaten::query()
            ->aktif()
            ->orderBy('kode')
            ->get(['id', 'provinsi_id', 'kode', 'tipe', 'nama']);

        // Data referensi kecamatan untuk cascading dropdown
        $kecamatanList = Kecamatan::query()
            ->aktif()
            ->orderBy('kode')
            ->get(['id', 'kabupaten_id', 'kode', 'nama']);

        return view('wilayah.kelurahan.index', compact(
            'kelurahanList',
            'provinsiList',
            'kabupatenList',
            'kecamatanList',
            'perPageInput'
        ));
    }

    /**
     * Menyimpan data wilayah kelurahan/desa baru ke dalam sistem.
     */
    public function store(StoreKelurahanRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['status'] = $request->boolean('status');

        $kelurahan = Kelurahan::create($data);

        return redirect()
            ->route('wilayah.kelurahan')
            ->with('success', "Data {$kelurahan->nama_lengkap} berhasil ditambahkan ke dalam sistem.");
    }

    /**
     * Menampilkan form edit (dialihkan ke halaman index karena menggunakan modal in-place).
     */
    public function edit(Kelurahan $kelurahan): RedirectResponse
    {
        return redirect()->route('wilayah.kelurahan');
    }

    /**
     * Memperbarui data wilayah kelurahan/desa yang ditentukan.
     */
    public function update(UpdateKelurahanRequest $request, Kelurahan $kelurahan): RedirectResponse
    {
        $data = $request->validated();
        $data['status'] = $request->boolean('status');

        $kelurahan->update($data);

        return redirect()
            ->route('wilayah.kelurahan')
            ->with('success', "Data {$kelurahan->nama_lengkap} berhasil diperbarui.");
    }

    /**
     * Menghapus data wilayah kelurahan/desa dari sistem.
     * Mencegah penghapusan jika kelurahan/desa masih ditautkan ke data sekolah aktif.
     */
    public function destroy(Kelurahan $kelurahan): RedirectResponse
    {
        // Proteksi integritas relasi: periksa apakah nama atau nama lengkap digunakan di data sekolah
        $isLinkedToSchool = School::query()
            ->where('village', $kelurahan->nama)
            ->orWhere('village', $kelurahan->nama_lengkap)
            ->exists();

        if ($isLinkedToSchool) {
            return redirect()
                ->route('wilayah.kelurahan')
                ->with('error', "{$kelurahan->nama_lengkap} tidak dapat dihapus karena masih digunakan oleh data sekolah aktif.");
        }

        $namaLengkap = $kelurahan->nama_lengkap;
        $kelurahan->delete();

        return redirect()
            ->route('wilayah.kelurahan')
            ->with('success', "Data {$namaLengkap} berhasil dihapus dari sistem.");
    }
}
