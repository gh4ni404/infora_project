<?php

namespace App\Http\Controllers\Wilayah;

use App\Http\Controllers\Controller;
use App\Http\Requests\Wilayah\StoreKecamatanRequest;
use App\Http\Requests\Wilayah\UpdateKecamatanRequest;
use App\Models\Kabupaten;
use App\Models\Kecamatan;
use App\Models\Provinsi;
use App\Models\School;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KecamatanController extends Controller
{
    /**
     * Menampilkan daftar wilayah kecamatan dengan filter provinsi, kabupaten, status, pencarian, dan paginasi.
     */
    public function index(Request $request): View
    {
        $query = Kecamatan::query()
            ->with(['kabupaten.provinsi'])
            ->orderBy('kode')
            ->orderBy('nama');

        // Filter pencarian universal (kode, nama kecamatan, nama kabupaten, nama provinsi)
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

        $kecamatanList = $query->paginate($perPage)->withQueryString();

        // Data referensi provinsi untuk dropdown filter dan dropdown form modal
        $provinsiList = Provinsi::query()
            ->aktif()
            ->orderBy('kode')
            ->get(['id', 'kode', 'nama']);

        // Data referensi kabupaten untuk cascading dropdown
        $kabupatenList = Kabupaten::query()
            ->aktif()
            ->orderBy('kode')
            ->get(['id', 'provinsi_id', 'kode', 'tipe', 'nama']);

        return view('wilayah.kecamatan.index', compact('kecamatanList', 'provinsiList', 'kabupatenList', 'perPageInput'));
    }

    /**
     * Menyimpan data wilayah kecamatan baru ke dalam sistem.
     */
    public function store(StoreKecamatanRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['status'] = $request->boolean('status');

        $kecamatan = Kecamatan::create($data);

        return redirect()
            ->route('wilayah.kecamatan')
            ->with('success', "Data {$kecamatan->nama_lengkap} berhasil ditambahkan ke dalam sistem.");
    }

    /**
     * Menampilkan form edit (dialihkan ke halaman index karena menggunakan modal in-place).
     */
    public function edit(Kecamatan $kecamatan): RedirectResponse
    {
        return redirect()->route('wilayah.kecamatan');
    }

    /**
     * Memperbarui data wilayah kecamatan yang ditentukan.
     */
    public function update(UpdateKecamatanRequest $request, Kecamatan $kecamatan): RedirectResponse
    {
        $data = $request->validated();
        $data['status'] = $request->boolean('status');

        $kecamatan->update($data);

        return redirect()
            ->route('wilayah.kecamatan')
            ->with('success', "Data {$kecamatan->nama_lengkap} berhasil diperbarui.");
    }

    /**
     * Menghapus data wilayah kecamatan dari sistem.
     * Mencegah penghapusan jika kecamatan masih ditautkan ke data sekolah aktif.
     */
    public function destroy(Kecamatan $kecamatan): RedirectResponse
    {
        // Proteksi integritas relasi: periksa apakah nama atau nama lengkap kecamatan digunakan di data sekolah
        $isLinkedToSchool = School::query()
            ->where('district', $kecamatan->nama)
            ->orWhere('district', $kecamatan->nama_lengkap)
            ->exists();

        if ($isLinkedToSchool) {
            return redirect()
                ->route('wilayah.kecamatan')
                ->with('error', "{$kecamatan->nama_lengkap} tidak dapat dihapus karena masih digunakan oleh data sekolah aktif.");
        }

        $namaLengkap = $kecamatan->nama_lengkap;
        $kecamatan->delete();

        return redirect()
            ->route('wilayah.kecamatan')
            ->with('success', "Data {$namaLengkap} berhasil dihapus dari sistem.");
    }
}
