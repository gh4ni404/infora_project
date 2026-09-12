<?php

namespace App\Http\Controllers\Wilayah;

use App\Http\Controllers\Controller;
use App\Http\Requests\Wilayah\StoreProvinsiRequest;
use App\Http\Requests\Wilayah\UpdateProvinsiRequest;
use App\Models\Provinsi;
use App\Models\School;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProvinsiController extends Controller
{
    /**
     * Menampilkan daftar wilayah provinsi dengan fitur pencarian, filter status, dan paginasi.
     */
    public function index(Request $request): View
    {
        $query = Provinsi::query()
            ->orderBy('kode')
            ->orderBy('nama');

        if ($request->filled('search')) {
            $query->search($request->query('search'));
        }

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

        $provinsiList = $query->paginate($perPage)->withQueryString();

        return view('wilayah.provinsi.index', compact('provinsiList', 'perPageInput'));
    }

    /**
     * Menyimpan data wilayah provinsi baru ke dalam sistem.
     */
    public function store(StoreProvinsiRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['status'] = $request->boolean('status');

        Provinsi::create($data);

        return redirect()
            ->route('wilayah.provinsi')
            ->with('success', 'Data provinsi berhasil ditambahkan ke dalam sistem.');
    }

    /**
     * Menampilkan form edit (dialihkan ke halaman index karena menggunakan modal).
     */
    public function edit(Provinsi $provinsi): RedirectResponse
    {
        return redirect()->route('wilayah.provinsi');
    }

    /**
     * Memperbarui data wilayah provinsi yang ditentukan.
     */
    public function update(UpdateProvinsiRequest $request, Provinsi $provinsi): RedirectResponse
    {
        $data = $request->validated();
        $data['status'] = $request->boolean('status');

        $provinsi->update($data);

        return redirect()
            ->route('wilayah.provinsi')
            ->with('success', 'Data provinsi berhasil diperbarui.');
    }

    /**
     * Menghapus data wilayah provinsi dari sistem.
     * Mencegah penghapusan jika provinsi masih ditautkan ke data sekolah atau relasi lain.
     */
    public function destroy(Provinsi $provinsi): RedirectResponse
    {
        // Proteksi integritas relasi: periksa apakah provinsi masih menaungi data kabupaten/kota
        if ($provinsi->kabupaten()->exists()) {
            return redirect()
                ->route('wilayah.provinsi')
                ->with('error', "Provinsi {$provinsi->nama} tidak dapat dihapus karena masih memiliki relasi data kabupaten/kota aktif.");
        }

        // Proteksi integritas relasi: periksa apakah nama atau kode provinsi digunakan di data sekolah
        $isLinkedToSchool = School::where('province', $provinsi->nama)->exists();

        if ($isLinkedToSchool) {
            return redirect()
                ->route('wilayah.provinsi')
                ->with('error', "Provinsi {$provinsi->nama} tidak dapat dihapus karena masih digunakan oleh data sekolah aktif.");
        }

        $namaProvinsi = $provinsi->nama;
        $provinsi->delete();

        return redirect()
            ->route('wilayah.provinsi')
            ->with('success', "Data provinsi {$namaProvinsi} berhasil dihapus dari sistem.");
    }
}
