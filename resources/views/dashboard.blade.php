@extends('layouts.app')

@section('content')
<div class="card-surface">
    <div class="card-header">
        <div>
            <h2 class="user-name-text">Dashboard Super Administrator</h2>
            <div class="brand-subtitle">Tata Kelola Pusat Platform & Infrastruktur SIM INFORA</div>
        </div>
        <span class="badge badge-success">Sistem Aktif</span>
    </div>

    <div class="card-body">
        <div class="alert-info">
            <strong>Catatan Otoritas:</strong> Akun ini berstatus <strong>Super Administrator</strong> (Entitas Pengembang / Pemilik Platform, bukan pihak sekolah). Seluruh pengaturan menu sekolah akan dikonfigurasi pada tahap pengembangan selanjutnya.
        </div>

        <div class="card-surface">
            <div class="card-header">
                <span class="user-name-text">Ringkasan Lingkungan Sistem</span>
                <span class="badge badge-cyan">Infrastruktur</span>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <span class="form-label">Nama Akun Aktif:</span>
                    <span class="user-name-text">{{ $user->name }} ({{ $user->email }})</span>
                </div>
                <div class="form-group">
                    <span class="form-label">Username Pengembang:</span>
                    <span class="text-cyan">{{ $user->username }}</span>
                </div>
                <div class="form-group">
                    <span class="form-label">Tipe Akun:</span>
                    <span class="text-success">{{ strtoupper($user->user_type) }}</span>
                </div>
                <div class="form-group">
                    <span class="form-label">Framework & Runtime:</span>
                    <span class="text-muted">Laravel v{{ $systemInfo['laravel_version'] }} (PHP v{{ $systemInfo['php_version'] }})</span>
                </div>
                <div class="form-group">
                    <span class="form-label">Environment:</span>
                    <span class="text-muted">{{ strtoupper($systemInfo['environment']) }}</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
