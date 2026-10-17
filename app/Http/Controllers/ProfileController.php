<?php

namespace App\Http\Controllers;

use App\Http\Requests\Profile\UpdatePasswordRequest;
use App\Http\Requests\Profile\UpdateProfileRequest;
use App\Models\Gtk;
use App\Models\Provinsi;
use App\Support\ImageUploader;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Tampilkan halaman pengaturan profil pengguna mandiri.
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $user->loadMissing([
            'school',
            'gtk.provinsi',
            'gtk.kabupaten',
            'gtk.kecamatan',
            'gtk.kelurahan',
        ]);

        // Hubungkan otomatis jika ada data GTK di sekolah yang cocok tetapi belum ditautkan
        if (! $user->gtk) {
            $matchingGtk = Gtk::whereNull('user_id')
                ->where(function ($q) use ($user) {
                    if (filled($user->email)) {
                        $q->where('email', $user->email);
                    }
                    if (filled($user->username)) {
                        $q->orWhere('nip', $user->username)
                            ->orWhere('nuptk', $user->username)
                            ->orWhere('nik', $user->username);
                    }
                })
                ->when($user->school_id, fn ($q) => $q->where('school_id', $user->school_id))
                ->first();

            if ($matchingGtk) {
                $matchingGtk->update(['user_id' => $user->id]);
                $user->load([
                    'gtk.provinsi',
                    'gtk.kabupaten',
                    'gtk.kecamatan',
                    'gtk.kelurahan',
                ]);
            }
        }

        $activeTab = $request->query('tab', 'personal');
        $validTabs = ['personal', 'employment', 'contact', 'account', 'status', 'photo', 'security', 'official'];
        if (! in_array($activeTab, $validTabs, true)) {
            $activeTab = 'personal';
        }

        // Kompatibilitas alias tab lama
        if ($activeTab === 'security' || $activeTab === 'account') {
            $activeTab = 'personal';
        } elseif ($activeTab === 'official') {
            $activeTab = 'employment';
        } elseif ($activeTab === 'photo') {
            $activeTab = 'status';
        }

        $provinsiList = Provinsi::query()
            ->aktif()
            ->orderBy('kode')
            ->get(['id', 'kode', 'nama']);

        $statusKepegawaianList = [
            'PNS',
            'PPPK',
            'GTY/PTY (Tetap Yayasan)',
            'GTT/PTT (Tidak Tetap)',
            'Honorer Sekolah',
        ];

        $pendidikanList = [
            'SMA / SMK',
            'Diploma (D3)',
            'Sarjana / Diploma IV (S1/D4)',
            'Magister (S2)',
            'Doktor (S3)',
        ];

        return view('profile.index', [
            'user' => $user,
            'gtk' => $user->gtk,
            'school' => $user->school,
            'activeTab' => $activeTab,
            'provinsiList' => $provinsiList,
            'statusKepegawaianList' => $statusKepegawaianList,
            'pendidikanList' => $pendidikanList,
        ]);
    }

    /**
     * Perbarui data identitas pribadi, kepegawaian, kontak, kredensial, dan foto avatar.
     */
    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        $user = $request->user();
        $user->loadMissing(['gtk', 'school']);

        if (! $user->gtk) {
            $matchingGtk = Gtk::whereNull('user_id')
                ->where(function ($q) use ($user) {
                    if (filled($user->email)) {
                        $q->where('email', $user->email);
                    }
                    if (filled($user->username)) {
                        $q->orWhere('nip', $user->username)
                            ->orWhere('nuptk', $user->username)
                            ->orWhere('nik', $user->username);
                    }
                })
                ->when($user->school_id, fn ($q) => $q->where('school_id', $user->school_id))
                ->first();

            if ($matchingGtk) {
                $matchingGtk->update(['user_id' => $user->id]);
                $user->load('gtk');
            }
        }

        $validated = $request->validated();
        $gtk = $user->gtk;

        // 1. Update nama lengkap pada User
        $namaLengkap = $validated['nama_lengkap'] ?? $validated['name'] ?? null;
        if ($namaLengkap !== null) {
            $user->name = $namaLengkap;
        }

        // 2. Update email pada User
        if (array_key_exists('email', $validated)) {
            $user->email = $validated['email'] ?: null;
        }

        // 3. Update username pada User
        if (! empty($validated['username'])) {
            $user->username = $validated['username'];
        }

        // 4. Tangani foto avatar profil
        if ($request->boolean('remove_foto')) {
            if ($user->avatar_path) {
                ImageUploader::delete($user->avatar_path);
                $user->avatar_path = null;
            }

            if ($gtk && $gtk->foto_path) {
                ImageUploader::delete($gtk->foto_path);
                $gtk->foto_path = null;
            }
        } elseif (! empty($validated['foto'])) {
            $newAvatarPath = ImageUploader::uploadBase64(
                base64String: $validated['foto'],
                directory: 'photos/avatar',
                module: 'avatar',
                userId: $user->id
            );

            if ($newAvatarPath) {
                if ($user->avatar_path) {
                    ImageUploader::delete($user->avatar_path);
                }
                $user->avatar_path = $newAvatarPath;

                if ($gtk) {
                    $gtk->foto_path = $newAvatarPath;
                }
            }
        }

        $user->save();

        // 5. Perbarui data GTK jika akun terhubung ke profil GTK
        if ($gtk) {
            $gtkData = [];

            if ($namaLengkap !== null) {
                $gtkData['nama_lengkap'] = $namaLengkap;
            }

            // Informasi Pribadi
            foreach (['gelar_depan', 'gelar_belakang', 'jenis_kelamin', 'nik', 'tempat_lahir', 'tanggal_lahir', 'agama'] as $field) {
                if (array_key_exists($field, $validated)) {
                    $gtkData[$field] = $validated[$field] ?: null;
                }
            }

            // Klasifikasi Kepegawaian & Kualifikasi Pendidikan
            foreach (['status_kepegawaian', 'nip', 'nuptk', 'pendidikan_terakhir', 'jurusan_pendidikan'] as $field) {
                if (array_key_exists($field, $validated)) {
                    $gtkData[$field] = $validated[$field] ?: null;
                }
            }

            // Kontak & Wilayah Domisili
            foreach (['no_hp', 'provinsi_id', 'kabupaten_id', 'kecamatan_id', 'kelurahan_id', 'alamat'] as $field) {
                if (array_key_exists($field, $validated)) {
                    $gtkData[$field] = $validated[$field] ?: null;
                }
            }

            if (array_key_exists('email', $validated)) {
                $gtkData['email'] = $user->email;
            }

            if ($gtk->isDirty('foto_path')) {
                $gtkData['foto_path'] = $gtk->foto_path;
            }

            if (! empty($gtkData)) {
                $gtk->update($gtkData);
            }
        }

        $activeTab = $request->input('tab', 'personal');
        if ($activeTab === 'account' || $activeTab === 'security') {
            $activeTab = 'personal';
        } elseif ($activeTab === 'official') {
            $activeTab = 'employment';
        } elseif ($activeTab === 'photo') {
            $activeTab = 'status';
        }

        return redirect()
            ->route('profile.index', ['tab' => $activeTab])
            ->with('success', 'Profil Anda berhasil diperbarui.');
    }

    /**
     * Perbarui kata sandi akun pengguna dan lakukan logout otomatis.
     */
    public function updatePassword(UpdatePasswordRequest $request): RedirectResponse
    {
        $user = $request->user();

        $user->password = Hash::make($request->validated('password'));
        $user->save();

        // Alur otomatis logout sesuai kesepakatan desain
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()
            ->route('login')
            ->with('success', 'Kata sandi berhasil diperbarui. Silakan masuk kembali menggunakan kata sandi baru Anda.');
    }
}
