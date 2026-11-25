<?php

namespace App\Http\Controllers\System;

use App\Http\Controllers\Controller;
use App\Http\Requests\System\StoreReferensiItemRequest;
use App\Http\Requests\System\StoreReferensiKategoriRequest;
use App\Http\Requests\System\UpdateReferensiRequest;
use App\Models\Jurusan;
use App\Models\Referensi;
use App\Models\School;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReferensiController extends Controller
{
    /**
     * Tampilkan antarmuka manajemen Data Referensi Sistem (Master-Detail dua kolom).
     */
    public function index(Request $request): View
    {
        $categories = Referensi::categories()
            ->withCount('children')
            ->orderBy('id')
            ->get();

        $selectedCategoryId = (int) $request->query('kategori_id', 0);
        $activeCategory = $selectedCategoryId > 0
            ? $categories->firstWhere('id', $selectedCategoryId)
            : $categories->first();

        $itemsQuery = Referensi::query()
            ->where('referensi_id', $activeCategory?->id)
            ->orderBy('id');

        if ($request->filled('search')) {
            $search = trim((string) $request->query('search'));
            $itemsQuery->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                    ->orWhere('kode', 'like', "%{$search}%");
            });
        }

        $items = $itemsQuery->paginate(25)->withQueryString();

        return view('system.referensi.index', compact('categories', 'activeCategory', 'items'));
    }

    /**
     * Simpan kategori referensi induk baru.
     */
    public function storeKategori(StoreReferensiKategoriRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['referensi_id'] = null;
        $data['is_system'] = false;
        $data['extra_attributes'] = null;

        $category = Referensi::create($data);
        Referensi::clearCache();

        return redirect()
            ->route('system.referensi.index', ['kategori_id' => $category->id])
            ->with('success', "Kategori referensi '{$category->nama}' berhasil ditambahkan.");
    }

    /**
     * Perbarui data kategori referensi induk.
     */
    public function updateKategori(UpdateReferensiRequest $request, Referensi $referensi): RedirectResponse
    {
        if ($referensi->referensi_id !== null) {
            return redirect()
                ->route('system.referensi.index')
                ->with('error', 'Referensi yang dipilih bukan merupakan kategori induk.');
        }

        $data = [
            'nama' => $request->input('nama'),
            'is_active' => $request->boolean('is_active', true),
        ];

        // Jika bukan kategori sistem, kode boleh diperbarui
        if (! $referensi->is_system) {
            $data['kode'] = strtolower(trim((string) $request->input('kode')));
        }

        $referensi->update($data);
        Referensi::clearCache();

        return redirect()
            ->route('system.referensi.index', ['kategori_id' => $referensi->id])
            ->with('success', "Kategori referensi '{$referensi->nama}' berhasil diperbarui.");
    }

    /**
     * Hapus kategori referensi beserta seluruh item anaknya (jika bukan kategori sistem).
     */
    public function destroyKategori(Referensi $referensi): RedirectResponse
    {
        if ($referensi->referensi_id !== null) {
            return redirect()
                ->route('system.referensi.index')
                ->with('error', 'Target bukan merupakan kategori induk.');
        }

        if ($referensi->is_system) {
            return redirect()
                ->route('system.referensi.index', ['kategori_id' => $referensi->id])
                ->with('error', "Kategori '{$referensi->nama}' merupakan bawaan sistem dan tidak dapat dihapus.");
        }

        // Periksa apakah ada item anak yang sedang digunakan di data sekolah atau jurusan
        $childCodes = $referensi->children()->pluck('kode')->toArray();

        if (! empty($childCodes)) {
            $isUsedInSchool = School::whereIn('school_type', $childCodes)
                ->orWhereIn('accreditation', $childCodes)
                ->exists();

            $isUsedInJurusan = Jurusan::whereIn('jenjang', $childCodes)->exists();

            if ($isUsedInSchool || $isUsedInJurusan) {
                return redirect()
                    ->route('system.referensi.index', ['kategori_id' => $referensi->id])
                    ->with('error', "Kategori '{$referensi->nama}' tidak dapat dihapus karena beberapa item di dalamnya masih digunakan oleh data sekolah atau jurusan aktif.");
            }
        }

        $nama = $referensi->nama;
        $referensi->delete();
        Referensi::clearCache();

        return redirect()
            ->route('system.referensi.index')
            ->with('success', "Kategori referensi '{$nama}' dan seluruh item di dalamnya berhasil dihapus.");
    }

    /**
     * Simpan item referensi baru ke dalam kategori aktif.
     */
    public function storeItem(StoreReferensiItemRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $parent = Referensi::findOrFail($data['referensi_id']);

        $extraAttributes = null;
        if ($parent->kode === 'school_type') {
            $extraAttributes = [
                'is_madrasah' => $request->boolean('is_madrasah', false),
            ];
        }

        $item = Referensi::create([
            'referensi_id' => $parent->id,
            'nama' => $data['nama'],
            'kode' => $data['kode'],
            'is_active' => $data['is_active'],
            'is_system' => false,
            'extra_attributes' => $extraAttributes,
        ]);

        Referensi::clearCache();

        return redirect()
            ->route('system.referensi.index', ['kategori_id' => $parent->id])
            ->with('success', "Item referensi '{$item->nama}' ({$item->kode}) berhasil ditambahkan ke kategori {$parent->nama}.");
    }

    /**
     * Perbarui data item referensi.
     */
    public function updateItem(UpdateReferensiRequest $request, Referensi $referensi): RedirectResponse
    {
        if ($referensi->referensi_id === null) {
            return redirect()
                ->route('system.referensi.index')
                ->with('error', 'Target bukan merupakan item referensi.');
        }

        $parent = $referensi->parent;
        $data = [
            'nama' => $request->input('nama'),
            'is_active' => $request->boolean('is_active', true),
        ];

        // Jika bukan item sistem, kode boleh diperbarui
        if (! $referensi->is_system) {
            $data['kode'] = trim((string) $request->input('kode'));
        }

        if ($parent && $parent->kode === 'school_type') {
            $data['extra_attributes'] = [
                'is_madrasah' => $request->boolean('is_madrasah', false),
            ];
        }

        $referensi->update($data);
        Referensi::clearCache();

        return redirect()
            ->route('system.referensi.index', ['kategori_id' => $referensi->referensi_id])
            ->with('success', "Item referensi '{$referensi->nama}' berhasil diperbarui.");
    }

    /**
     * Hapus item referensi (dengan validasi proteksi sistem dan relasi aktif).
     */
    public function destroyItem(Referensi $referensi): RedirectResponse
    {
        if ($referensi->referensi_id === null) {
            return redirect()
                ->route('system.referensi.index')
                ->with('error', 'Target bukan merupakan item referensi.');
        }

        $parent = $referensi->parent;
        $parentId = $referensi->referensi_id;

        if ($referensi->is_system) {
            return redirect()
                ->route('system.referensi.index', ['kategori_id' => $parentId])
                ->with('error', "Item referensi '{$referensi->nama}' ({$referensi->kode}) merupakan item sistem bawaan dan tidak dapat dihapus. Anda dapat menonaktifkannya jika tidak ingin digunakan.");
        }

        // Pengecekan relasi aktif
        if ($parent && $parent->kode === 'school_type') {
            $isUsed = School::where('school_type', $referensi->kode)->exists()
                || Jurusan::where('jenjang', $referensi->kode)->exists();

            if ($isUsed) {
                return redirect()
                    ->route('system.referensi.index', ['kategori_id' => $parentId])
                    ->with('error', "Item '{$referensi->nama}' ({$referensi->kode}) tidak dapat dihapus karena masih digunakan oleh data sekolah atau jurusan aktif.");
            }
        }

        if ($parent && $parent->kode === 'accreditation') {
            $isUsed = School::where('accreditation', $referensi->kode)->exists();
            if ($isUsed) {
                return redirect()
                    ->route('system.referensi.index', ['kategori_id' => $parentId])
                    ->with('error', "Akreditasi '{$referensi->nama}' ({$referensi->kode}) tidak dapat dihapus karena masih digunakan oleh data sekolah aktif.");
            }
        }

        $nama = $referensi->nama;
        $referensi->delete();
        Referensi::clearCache();

        return redirect()
            ->route('system.referensi.index', ['kategori_id' => $parentId])
            ->with('success', "Item referensi '{$nama}' berhasil dihapus.");
    }

    /**
     * Alihkan status aktif/non-aktif referensi.
     */
    public function toggleStatus(Referensi $referensi): RedirectResponse
    {
        $referensi->update([
            'is_active' => ! $referensi->is_active,
        ]);

        Referensi::clearCache();

        $statusText = $referensi->is_active ? 'diaktifkan' : 'dinonaktifkan';
        $targetKategoriId = $referensi->referensi_id ?: $referensi->id;

        return redirect()
            ->route('system.referensi.index', ['kategori_id' => $targetKategoriId])
            ->with('success', "Status '{$referensi->nama}' berhasil {$statusText}.");
    }
}
