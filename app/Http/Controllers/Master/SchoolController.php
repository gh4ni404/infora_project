<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Http\Requests\Master\StoreSchoolRequest;
use App\Http\Requests\Master\UpdateSchoolRequest;
use App\Models\School;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class SchoolController extends Controller
{
    /**
     * Display a listing of schools.
     */
    public function index(Request $request): View
    {
        $query = School::query()
            ->orderBy('name')
            ->orderBy('id');

        if ($request->filled('search')) {
            $search = trim($request->query('search'));
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('npsn', 'like', "%{$search}%")
                    ->orWhere('city', 'like', "%{$search}%");
            });
        }

        if ($request->filled('school_type')) {
            $query->where('school_type', $request->query('school_type'));
        }

        $schools = $query->paginate(15)->withQueryString();

        return view('master.data-sekolah.index', compact('schools'));
    }

    /**
     * Store a newly created school in storage.
     */
    public function store(StoreSchoolRequest $request): RedirectResponse
    {
        $data = $request->validated();

        if (! empty($data['logo'])) {
            $data['logo_path'] = $this->processBase64Logo($data['logo']);
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
    public function edit(School $dataSekolah): View
    {
        return view('master.data-sekolah.edit', ['school' => $dataSekolah]);
    }

    /**
     * Update the specified school in storage.
     */
    public function update(UpdateSchoolRequest $request, School $dataSekolah): RedirectResponse
    {
        $data = $request->validated();

        // Handle logo removal
        if ($request->boolean('remove_logo') && $dataSekolah->logo_path) {
            $this->deleteLogoFile($dataSekolah->logo_path);
            $data['logo_path'] = null;
        }

        // Handle new logo upload (Base64)
        if (! empty($data['logo'])) {
            // Remove old logo if exists
            if ($dataSekolah->logo_path) {
                $this->deleteLogoFile($dataSekolah->logo_path);
            }
            $data['logo_path'] = $this->processBase64Logo($data['logo']);
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
            $this->deleteLogoFile($dataSekolah->logo_path);
        }

        $dataSekolah->delete();

        return redirect()
            ->route('master.data-sekolah.index')
            ->with('success', 'Data sekolah berhasil dihapus dari sistem.');
    }

    /**
     * Process Base64-encoded logo image and store to disk.
     * Naming convention: {modul}_u{user_id}_{YYYYMMDD_His}_{random_8char}.{ext}
     */
    private function processBase64Logo(string $base64String): ?string
    {
        if (! preg_match('/^data:image\/(png|jpe?g|gif|webp|svg\+xml);base64,/', $base64String, $matches)) {
            return null;
        }

        $extension = match ($matches[1]) {
            'jpeg', 'jpg' => 'jpg',
            'svg+xml' => 'svg',
            default => $matches[1],
        };

        $imageData = base64_decode(preg_replace('/^data:image\/[a-z+]+;base64,/', '', $base64String), true);
        if ($imageData === false) {
            return null;
        }

        // Validate file size (max 1MB)
        if (strlen($imageData) > 1048576) {
            return null;
        }

        $userId = auth()->id() ?? 0;
        $timestamp = now()->format('Ymd_His');
        $randomHash = Str::random(8);
        $filename = "sekolah_u{$userId}_{$timestamp}_{$randomHash}.{$extension}";

        $path = 'logos/'.$filename;
        Storage::disk('public')->put($path, $imageData);

        return $path;
    }

    /**
     * Delete a logo file from storage.
     */
    private function deleteLogoFile(string $logoPath): void
    {
        if (Storage::disk('public')->exists($logoPath)) {
            Storage::disk('public')->delete($logoPath);
        }
    }
}
