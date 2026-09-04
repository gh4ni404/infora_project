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
                    <input
                        type="password"
                        id="password"
                        name="password"
                        class="form-input"
                        placeholder="Masukkan kata sandi"
                        required
                        autocomplete="current-password"
                    >
                </div>

                <div class="form-group">
                    <button type="submit" class="btn-primary btn-block">
                        Masuk ke Dashboard
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
