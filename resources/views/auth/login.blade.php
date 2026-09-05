@extends('layouts.auth')

@section('content')
<div class="auth-card-box">
    <div class="auth-header-logo">
        <h1 class="brand-title">INFORA</h1>
        <div class="brand-subtitle">Information Network for Organization, Records, & Accreditation</div>
    </div>

    <div class="card-surface">
        <div class="card-header">
            <div>
                <h2 class="user-name-text">Masuk ke Sistem</h2>
                <div class="brand-subtitle">Portal Tata Kelola & Layanan SIM</div>
            </div>
            <span class="badge badge-cyan">Platform</span>
        </div>

        <div class="card-body">
            @if ($errors->any())
                <div class="alert-danger">
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('login.attempt') }}">
                @csrf

                <div class="form-group">
                    <label for="login" class="form-label">Username atau Email</label>
                    <input
                        type="text"
                        id="login"
                        name="login"
                        class="form-input"
                        value="{{ old('login') }}"
                        placeholder="Contoh: superadmin atau email@domain.com"
                        required
                        autofocus
                        autocomplete="username"
                    >
                </div>

                <div class="form-group">
                    <label for="password" class="form-label">Kata Sandi</label>
                    <div class="input-password-wrapper">
                        <input
                            type="password"
                            id="password"
                            name="password"
                            class="form-input input-with-action"
                            placeholder="Masukkan kata sandi"
                            required
                            autocomplete="current-password"
                        >
                        <button
                            type="button"
                            id="togglePassword"
                            class="btn-input-action"
                            aria-label="Tampilkan kata sandi"
                            title="Tampilkan kata sandi"
                        >
                            <!-- Eye icon (shown when password is hidden) -->
                            <svg class="icon-eye" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"></path>
                                <circle cx="12" cy="12" r="3"></circle>
                            </svg>
                            <!-- Eye Off icon (shown when password is visible) -->
                            <svg class="icon-eye-off hidden" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"></path>
                                <path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"></path>
                                <path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"></path>
                                <line x1="2" x2="22" y1="2" y2="22"></line>
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="form-group">
                    <button type="submit" id="btnLogin" class="btn-primary btn-block">
                        <svg class="spinner-icon hidden" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M21 12a9 9 0 1 1-6.219-8.56"></path>
                        </svg>
                        <span class="btn-text">Masuk ke Dashboard</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
