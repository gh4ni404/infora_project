{{-- Modal Kelola Sampel Wajah Terdaftar Per Murid --}}
<x-modal
    id="modalManageFaces"
    size="lg"
    title="Kelola Sampel Wajah Murid"
    subtitle="Lihat, aktifkan/nonaktifkan, atau hapus sampel wajah yang tersimpan untuk absensi"
    :with-form="false">

    {{-- Header Identitas Murid --}}
    <div class="card-surface border rounded-2xl p-4 bg-slate-50/70">
        <div class="flex items-center justify-between gap-4 flex-wrap">
            <div class="flex items-center gap-3.5 min-w-0">
                <div class="user-avatar-circle avatar-lg shrink-0" id="manageFacesAvatarWrap">
                    <img id="manageFacesAvatarImg" src="" alt="Foto Murid" class="hidden">
                    <span id="manageFacesInitials">MD</span>
                </div>
                <div class="min-w-0">
                    <h4 class="text-base font-bold text-slate-900 truncate" id="manageFacesMuridNama">-</h4>
                    <div class="text-xs text-slate-500 mt-0.5" id="manageFacesMuridMeta">-</div>
                    <div class="mt-2">
                        <span class="badge badge-cyan" id="manageFacesCountBadge">0 Aktif</span>
                    </div>
                </div>
            </div>

            @if ($canCreateFace)
                <button type="button" class="btn-primary" id="btnAddSampleFromManage">
                    <x-icon name="camera" size="16" />
                    <span>Tambah Sampel</span>
                </button>
            @endif
        </div>
    </div>

    {{-- Daftar Kartu Sampel Wajah --}}
    <div id="manageFacesEmpty" class="hidden">
        <div class="alert-info">
            <x-icon name="info" size="24" />
            <div>
                <strong>Belum Ada Sampel Wajah:</strong><br>
                Peserta didik ini belum memiliki sampel wajah terdaftar. Klik tombol <strong>Tambah Sampel</strong> di atas untuk mendaftarkan wajah.
            </div>
        </div>
    </div>

    <div id="manageFacesListContainer" class="face-sample-grid"></div>

    <x-slot:footer>
        <button type="button" class="btn-secondary" id="btnCloseManageFaces">Tutup</button>
    </x-slot:footer>
</x-modal>
