<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Http\Requests\Master\UpdateSchoolRequest;
use App\Models\Provinsi;
use App\Models\School;
use App\Support\AdminAccountManager;
use App\Support\ImageUploader;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SchoolController extends Controller
{
    /**
     * Tampilkan profil sekolah tunggal beserta relasi wilayah dan akun admin.
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        [$school] = $this->resolveSchoolContext($request);

        if ($school) {
            $school->loadMissing(['provinsi', 'kabupaten', 'kecamatan', 'kelurahan', 'adminUser']);
        }

        // Data referensi provinsi untuk inisialisasi dropdown wilayah pada modal edit
        $provinsiList = Provinsi::query()
            ->aktif()
            ->orderBy('kode')
            ->get(['id', 'kode', 'nama']);

        return view('master.data-sekolah.index', compact('school', 'provinsiList', 'user'));
    }

    /**
     * Arahkan rute edit ke halaman profil sekolah.
     */
    public function edit(School $dataSekolah): RedirectResponse
    {
        return redirect()->route('master.data-sekolah.index');
    }

    /**
     * Perbarui data profil sekolah tunggal.
     */
    public function update(UpdateSchoolRequest $request, School $dataSekolah): RedirectResponse
    {
        $this->authorizeSchool($request, $dataSekolah);

        $data = $request->validated();

        if ($request->boolean('remove_logo') && $dataSekolah->logo_path) {
            ImageUploader::delete($dataSekolah->logo_path);
            $data['logo_path'] = null;
        }

        if (! empty($data['logo'])) {
            if ($dataSekolah->logo_path) {
                ImageUploader::delete($dataSekolah->logo_path);
            }
            $data['logo_path'] = ImageUploader::uploadBase64($data['logo'], 'logos', 'sekolah');
        }
        unset($data['logo'], $data['remove_logo']);

        $dataSekolah->update($data);

        // Selaraskan data akun administrator sekolah jika status atau NPSN berubah
        AdminAccountManager::createOrSyncAdmin($dataSekolah);

        return redirect()
            ->route('master.data-sekolah.index')
            ->with('success', 'Data sekolah berhasil diperbarui.');
    }

    /**
     * Terbitkan atau selaraskan akun administrator untuk profil sekolah aktif.
     */
    public function syncSingleAdmin(Request $request, School $dataSekolah): RedirectResponse
    {
        $this->authorizeSchool($request, $dataSekolah);

        $admin = AdminAccountManager::createOrSyncAdmin($dataSekolah);

        return redirect()
            ->route('master.data-sekolah.index')
            ->with('success', "Akun administrator sekolah {$dataSekolah->name} (Username: {$admin->username}) berhasil disinkronkan.");
    }
}
