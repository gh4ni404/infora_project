<?php

namespace App\Http\Controllers\Wilayah;

use App\Http\Controllers\Controller;
use App\Http\Requests\Wilayah\StoreKabupatenRequest;
use App\Http\Requests\Wilayah\UpdateKabupatenRequest;
use App\Models\Kabupaten;
use App\Models\Provinsi;
use App\Models\School;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KabupatenController extends Controller
{
    /**
     * Menampilkan daftar wilayah kabupaten/kota dengan filter provinsi, tipe, status, pencarian, dan paginasi.
     */
    public function index(Request $request): View
    {
        $query = Kabupaten::query()
            ->with('provinsi')
            ->orderBy('kode')
            ->orderBy('nama');

        // Filter pencarian universal (kode, nama kabupaten, nama provinsi)
        if ($request->filled('search')) {
            $query->search($request->query('search'));
        }

        // Filter berdasarkan provinsi induk
        if ($request->filled('provinsi_id')) {
            $query->filterByProvinsi((int) $request->query('provinsi_id'));
        }

        // Filter berdasarkan tipe (Kabupaten / Kota)
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

        $kabupatenList = $query->paginate($perPage)->withQueryString();

        // Data referensi provinsi untuk dropdown filter dan dropdown form modal
        $provinsiList = Provinsi::query()
            ->aktif()
            ->orderBy('kode')
            ->get(['id', 'kode', 'nama']);

        return view('wilayah.kabupaten.index', compact('kabupatenList', 'provinsiList', 'perPageInput'));
    }

    /**
     * Menyimpan data wilayah kabupaten/kota baru ke dalam sistem.
     */
    public function store(StoreKabupatenRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['status'] = $request->boolean('status');

        $kabupaten = Kabupaten::create($data);

        return redirect()
            ->route('wilayah.kabupaten')
            ->with('success', "Data {$kabupaten->nama_lengkap} berhasil ditambahkan ke dalam sistem.");
    }

    /**
     * Menampilkan form edit (dialihkan ke halaman index karena menggunakan modal in-place).
     */
    public function edit(Kabupaten $kabupaten): RedirectResponse
    {
        return redirect()->route('wilayah.kabupaten');
    }

    /**
     * Memperbarui data wilayah kabupaten/kota yang ditentukan.
     */
    public function update(UpdateKabupatenRequest $request, Kabupaten $kabupaten): RedirectResponse
    {
        $data = $request->validated();
        $data['status'] = $request->boolean('status');

        $kabupaten->update($data);

        return redirect()
            ->route('wilayah.kabupaten')
            ->with('success', "Data {$kabupaten->nama_lengkap} berhasil diperbarui.");
    }

    /**
     * Menghapus data wilayah kabupaten/kota dari sistem.
     * Mencegah penghapusan jika kabupaten/kota masih ditautkan ke data sekolah aktif.
     */
    public function destroy(Kabupaten $kabupaten): RedirectResponse
    {
        // Proteksi integritas relasi: periksa apakah nama atau nama lengkap kabupaten digunakan di data sekolah
        $isLinkedToSchool = School::query()
            ->where('city', $kabupaten->nama)
            ->orWhere('city', $kabupaten->nama_lengkap)
            ->exists();

        if ($isLinkedToSchool) {
            return redirect()
                ->route('wilayah.kabupaten')
                ->with('error', "{$kabupaten->nama_lengkap} tidak dapat dihapus karena masih digunakan oleh data sekolah aktif.");
        }

        $namaLengkap = $kabupaten->nama_lengkap;
        $kabupaten->delete();

        return redirect()
            ->route('wilayah.kabupaten')
            ->with('success', "Data {$namaLengkap} berhasil dihapus dari sistem.");
    }
}
