<div class="card-surface">
    <div class="card-header">
        <div>
            <h3 class="font-weight-bold font-size-base mb-1">Status Keaktifan Akun</h3>
            <p class="brand-subtitle font-size-xs mb-0">Informasi status operasional dan hak akses akun Anda di dalam sistem SIM INFORA</p>
        </div>
    </div>

    <div class="card-body">
        <div class="form-section-label mb-2">Status Operasional & Hak Akses</div>
        <p class="brand-subtitle font-size-xs mb-3">Informasi status akun Anda yang terdaftar pada pangkalan data satuan pendidikan.</p>

        <div class="table-responsive">
            <table class="w-full text-sm">
                <tbody>
                    <tr class="border-b border-gray-100">
                        <td class="text-muted py-2 w-2/5">Status Akun Sistem</td>
                        <td class="py-2">
                            <span class="badge {{ $user->is_active ? 'badge-success' : 'badge-danger' }}">
                                {{ $user->is_active ? 'Akun Aktif' : 'Non-Aktif' }}
                            </span>
                        </td>
                    </tr>
                    <tr class="border-b border-gray-100">
                        <td class="text-muted py-2">Peran Pengguna</td>
                        <td class="py-2">
                            <span class="badge badge-role-{{ $user->user_type }}">
                                {{ match($user->user_type) {
                                    'super_admin' => 'Super Administrator',
                                    'admin' => 'Administrator Sekolah',
                                    'guru' => 'Pendidik / Tenaga Pengajar',
                                    'murid', 'siswa' => 'Peserta Didik',
                                    default => ucfirst($user->user_type)
                                } }}
                            </span>
                        </td>
                    </tr>
                    @if ($gtk)
                        <tr class="border-b border-gray-100">
                            <td class="text-muted py-2">Status Keaktifan GTK</td>
                            <td class="py-2">
                                <span class="badge {{ $gtk->is_active ? 'badge-success' : 'badge-danger' }}">
                                    {{ $gtk->is_active ? 'GTK Aktif Terdaftar' : 'Non-Aktif' }}
                                </span>
                            </td>
                        </tr>
                        <tr class="border-b border-gray-100">
                            <td class="text-muted py-2">Status Kepegawaian</td>
                            <td class="font-semibold text-main py-2">{{ $gtk->status_kepegawaian ?? '-' }}</td>
                        </tr>
                        @if ($gtk->nip)
                            <tr class="border-b border-gray-100">
                                <td class="text-muted py-2">NIP</td>
                                <td class="font-semibold text-main py-2">{{ $gtk->nip }}</td>
                            </tr>
                        @endif
                        @if ($gtk->nuptk)
                            <tr class="border-b border-gray-100">
                                <td class="text-muted py-2">NUPTK</td>
                                <td class="font-semibold text-main py-2">{{ $gtk->nuptk }}</td>
                            </tr>
                        @endif
                    @endif
                    @if ($school)
                        <tr class="border-b border-gray-100">
                            <td class="text-muted py-2">Satuan Pendidikan</td>
                            <td class="font-semibold text-main py-2">{{ $school->name }}</td>
                        </tr>
                    @endif
                    <tr>
                        <td class="text-muted py-2">Terdaftar Sejak</td>
                        <td class="text-main py-2">{{ $user->created_at?->translatedFormat('d F Y H:i') ?? '-' }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
