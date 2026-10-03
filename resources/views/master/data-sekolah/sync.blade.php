{{-- Modal Konfirmasi Sinkronisasi Akun Administrator --}}
<x-modal 
    id="modalSyncAdmins" 
    size="sm" 
    action="{{ route('master.data-sekolah.sync-admins') }}" 
    method="POST"
    form-id="formSyncAdmins">
    <div class="text-center py-4">
        <div
            class="w-16 h-16 rounded-full bg-blue-50 text-brand ring-8 ring-blue-50/60 flex items-center justify-center my-8 shrink-0 mx-auto">
            <x-icon name="users" size="52" class="animate-handdrawn" />
        </div>
        <h3 class="text-base font-bold text-slate-900 mb-2" id="modalSyncAdminsTitle">
            Sinkronkan Akun Admin Sekolah?
        </h3>
        <p class="text-sm text-slate-600 mb-4 px-2">
            Sistem akan menerbitkan atau menyelaraskan akun administrator (<code class="text-xs font-mono font-bold text-brand">user_type: admin</code>) untuk seluruh unit sekolah yang terdaftar.
        </p>
        <div class="p-3 bg-slate-50 border border-slate-200/90 rounded-xl text-left text-xs text-slate-600 space-y-1.5 mb-2">
            <div class="flex items-center gap-1.5 font-semibold text-slate-800">
                <x-icon name="info-circle" size="14" class="text-brand shrink-0" />
                <span>Ketentuan Akun Otomatis:</span>
            </div>
            <ul class="list-disc list-inside space-y-1 text-slate-600 pl-1">
                <li>Username & Password awal = <strong>NPSN</strong> masing-masing sekolah.</li>
                <li>Izin menu akun baru disesuaikan dengan template peran <strong>Admin Sekolah</strong> (izin kustom akun lama tetap aman).</li>
            </ul>
        </div>
    </div>

    <x-slot:footer>
        <div class="grid grid-cols-2 gap-3 w-full">
            <button type="button" class="btn-secondary w-full justify-center font-medium" id="btnCancelSyncAdmins">Batal</button>
            <button type="submit" class="btn-primary w-full justify-center font-medium" id="btnConfirmSyncAdmins">Lanjutkan</button>
        </div>
    </x-slot:footer>
</x-modal>