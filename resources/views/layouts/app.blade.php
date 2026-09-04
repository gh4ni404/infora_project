<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'INFORA') }} - Dashboard</title>

    <!-- Google Fonts Preconnect -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Styles / Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <div class="layout-container">
        <!-- Sidebar Navigation -->
        <aside class="layout-sidebar">
            <div class="sidebar-header">
                <h1 class="brand-title">INFORA</h1>
                <div class="brand-subtitle">Platform Governance Core</div>
            </div>

            <nav class="sidebar-menu">
                <div class="menu-category-label">Menu Utama</div>

                <!-- Single Menu Item: Dashboard -->
                <a href="{{ route('dashboard') }}" class="nav-link-item active">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect width="7" height="9" x="3" y="3" rx="1"></rect>
                        <rect width="7" height="5" x="14" y="3" rx="1"></rect>
                        <rect width="7" height="9" x="14" y="12" rx="1"></rect>
                        <rect width="7" height="5" x="3" y="16" rx="1"></rect>
                    </svg>
                    <span>Dashboard</span>
                </a>
            </nav>
        </aside>

        <!-- Main Content Area -->
        <div class="layout-content-wrapper">
            <!-- Topbar Header -->
            <header class="topbar-header">
                <div>
                    <span class="badge badge-cyan">Entitas Pengembang Platform</span>
                </div>

                <div class="topbar-user-section">
                    <div class="user-avatar-circle">
                        {{ strtoupper(substr(auth()->user()->name ?? 'SA', 0, 2)) }}
                    </div>
                    <div class="user-meta-info">
                        <span class="user-name-text">{{ auth()->user()->name }}</span>
                        <span class="brand-subtitle">@<span>{{ auth()->user()->username ?? 'superadmin' }}</span></span>
                    </div>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="btn-ghost-danger">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                                <polyline points="16 17 21 12 16 7"></polyline>
                                <line x1="21" x2="9" y1="12" y2="12"></line>
                            </svg>
                            <span>Keluar</span>
                        </button>
                    </form>
                </div>
            </header>

            <!-- Main Body -->
            <main class="layout-main-body">
                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>
