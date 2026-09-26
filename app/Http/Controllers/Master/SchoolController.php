<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Http\Requests\Master\StoreSchoolRequest;
use App\Http\Requests\Master\UpdateSchoolRequest;
use App\Models\Provinsi;
use App\Models\School;
use App\Support\ImageUploader;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SchoolController extends Controller
{
    /**
     * Display a listing of schools.
     */
    public function index(Request $request): View
    {
        $query = School::query()
            ->with(['provinsi', 'kabupaten', 'kecamatan', 'kelurahan'])
            ->orderBy('name')
            ->orderBy('id');

        if ($request->filled('search')) {
            $search = trim($request->query('search'));
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('npsn', 'like', "%{$search}%")
                    ->orWhere('city', 'like', "%{$search}%")
                    ->orWhereHas('kabupaten', fn ($k) => $k->where('nama', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('school_type')) {
            $query->where('school_type', $request->query('school_type'));
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

        $schools = $query->paginate($perPage)->withQueryString();

        // Data referensi provinsi untuk inisialisasi dropdown wilayah
        $provinsiList = Provinsi::query()
            ->aktif()
            ->orderBy('kode')
            ->get(['id', 'kode', 'nama']);

        return view('master.data-sekolah.index', compact('schools', 'perPageInput', 'provinsiList'));
    }

    /**
     * Store a newly created school in storage.
     */
    public function store(StoreSchoolRequest $request): RedirectResponse
    {
        $data = $request->validated();

        if (! empty($data['logo'])) {
            $data['logo_path'] = ImageUploader::uploadBase64($data['logo'], 'logos', 'sekolah');
        }
        unset($data['logo']);

        School::create($data);

        return redirect()
            ->route('master.data-sekolah.index')
            ->with('success', 'Data sekolah berhasil ditambahkan ke dalam sistem.');
    }

    /**
     * Show the form for editing the specified school.
     */
    public function edit(School $dataSekolah): RedirectResponse
    {
        return redirect()->route('master.data-sekolah.index');
    }

    /**
     * Update the specified school in storage.
     */
    public function update(UpdateSchoolRequest $request, School $dataSekolah): RedirectResponse
    {
        $data = $request->validated();

        // Handle logo removal
        if ($request->boolean('remove_logo') && $dataSekolah->logo_path) {
            ImageUploader::delete($dataSekolah->logo_path);
            $data['logo_path'] = null;
        }

        // Handle new logo upload (Base64)
        if (! empty($data['logo'])) {
            // Remove old logo if exists
            if ($dataSekolah->logo_path) {
                ImageUploader::delete($dataSekolah->logo_path);
            }
            $data['logo_path'] = ImageUploader::uploadBase64($data['logo'], 'logos', 'sekolah');
        }
        unset($data['logo'], $data['remove_logo']);

        $dataSekolah->update($data);

        return redirect()
            ->route('master.data-sekolah.index')
            ->with('success', 'Data sekolah berhasil diperbarui.');
    }

    /**
     * Remove the specified school from storage.
     */
    public function destroy(School $dataSekolah): RedirectResponse
    {
        // Remove logo file if exists
        if ($dataSekolah->logo_path) {
            ImageUploader::delete($dataSekolah->logo_path);
        }

        $dataSekolah->delete();

        return redirect()
            ->route('master.data-sekolah.index')
            ->with('success', 'Data sekolah berhasil dihapus dari sistem.');
    }
}
