@extends('layouts.auth')

@section('content')
<div class="auth-card-box">
    <div class="auth-header-logo">
        <h1 class="brand-title">INFORA</h1>
        <div class="brand-subtitle">Information Network for Organization, Records, dan Accreditation</div>
    </div>

    <div class="card-surface">
        <div class="card-header">
            <div>
                <h2 class="user-name-text">Masuk ke Sistem</h2>
                <div class="brand-subtitle">Portal Tata Kelola dan Layanan SIM</div>
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
                            <x-icon name="eye-on" class="icon-eye" size="20" />
                            <!-- Eye Off icon (shown when password is visible) -->
                            <x-icon name="eye-off" class="icon-eye-off hidden" size="20" />
                        </button>
                    </div>
                </div>

                <div class="form-group">
                    <button type="submit" id="btnLogin" class="btn-primary btn-block">
                        <x-icon name="loader" class="spinner-icon hidden" size="18" />
                        <span class="btn-text">Masuk ke Dashboard</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
