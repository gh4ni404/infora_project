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

    <!-- Favicon / Tab Browser Icon -->
    <link rel="icon" type="image/jpeg" href="{{ asset('infora-icon.jpg') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
    <link rel="apple-touch-icon" href="{{ asset('infora-icon.jpg') }}">

    <!-- Styles / Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <div class="layout-container">
        <!-- Sidebar Navigation -->
        <aside class="layout-sidebar" id="layoutSidebar">
            <div class="sidebar-header">
                <a href="{{ auth()->user()?->isAdmin() ? route('dashboard.admin') : route('dashboard') }}" class="sidebar-brand-link" title="INFORA - SIM Sekolah">
                    <img src="{{ asset('images/infora-emblem-badge.png') }}" alt="INFORA Logo" class="brand-badge-img">
                    <div class="sidebar-brand-text">
                        <h1 class="brand-title">INFORA</h1>
                        <div class="brand-subtitle">SIM Sekolah</div>
                    </div>
                </a>
            </div>

            <div class="sidebar-search">
                <div class="search-box" id="sidebarSearchBox" title="Cari menu...">
                    <x-icon name="search" size="16" class="search-icon" />
                    <input
                        type="text"
                        id="sidebarMenuSearch"
                        class="search-input"
                        placeholder="Cari menu..."
                        autocomplete="off"
                        aria-label="Cari menu di sidebar"
                    >
                    <button
                        type="button"
                        id="sidebarSearchClear"
                        class="search-clear hidden"
                        aria-label="Bersihkan pencarian"
                        title="Bersihkan pencarian"
                    >
                        <x-icon name="close" size="14" />
                    </button>
                </div>
            </div>

            <nav class="sidebar-menu" id="sidebarNavMenu">
                @if (isset($sidebarModules) && $sidebarModules->isNotEmpty())
                    @foreach ($sidebarModules as $module)
                        @if ($module->menus->isNotEmpty())
                            <div class="menu-category-label" data-module-id="{{ $module->id }}">{{ $module->name }}</div>

                            @foreach ($module->menus as $menu)
                                @php
                                    $hasSubMenus = $menu->subMenus && $menu->subMenus->count() > 0;
                                    $isMenuActive = $hasSubMenus ? $menu->hasActiveSubMenu() : $menu->isRouteActive();
                                @endphp

                                @if ($hasSubMenus)
                                    <div class="nav-group-item {{ $isMenuActive ? 'is-open active-parent' : '' }}" data-menu-id="{{ $menu->id }}">
                                        <button type="button" class="nav-group-trigger {{ $isMenuActive ? 'active' : '' }}" aria-expanded="{{ $isMenuActive ? 'true' : 'false' }}" title="{{ $menu->name }}">
                                            <x-icon :name="$menu->icon" class="nav-item-icon" />
                                            <span class="nav-item-title">{{ $menu->name }}</span>
                                            <!-- <span class="nav-count-badge">{{ $menu->subMenus->count() }}</span> -->
                                            <x-icon name="chevron-down" size="16" class="nav-arrow-icon" />
                                        </button>
                                        <div class="nav-submenu-list">
                                            @foreach ($menu->subMenus as $subMenu)
                                                @php
                                                    $isSubActive = $subMenu->isRouteActive();
                                                    $subHref = $subMenu->route_url;
                                                @endphp
                                                <a href="{{ $subHref }}" class="nav-submenu-item {{ $isSubActive ? 'active' : '' }}" title="{{ $subMenu->name }}">
                                                    <span class="nav-submenu-text">{{ $subMenu->name }}</span>
                                                </a>
                                            @endforeach
                                        </div>
                                    </div>
                                @else
                                    <a href="{{ $menu->route_url }}" class="nav-link-item {{ $isMenuActive ? 'active' : '' }}" title="{{ $menu->name }}">
                                        <x-icon :name="$menu->icon" class="nav-item-icon" />
                                        <span>{{ $menu->name }}</span>
                                    </a>
                                @endif
                            @endforeach
                        @endif
                    @endforeach
                @else
                    <div class="menu-category-label">Menu Utama</div>
                    <a href="{{ auth()->user()?->isAdmin() ? route('dashboard.admin') : route('dashboard') }}" class="nav-link-item active" title="Dashboard">
                        <x-icon name="dashboard" class="nav-item-icon" />
                        <span>Dashboard</span>
                    </a>
                @endif

                <div id="menuSearchEmpty" class="empty-state hidden">
                    <x-icon name="search-minus" class="empty-state-icon" size="22" />
                    <span class="empty-state-text">Menu tidak ditemukan</span>
                </div>
            </nav>

            <div class="sidebar-footer" id="sidebarFooter">
                <div class="dropdown dropdown-full" id="userMenuDropdown">
                    <!-- Reusable Universal Dropdown Menu (Upward) -->
                    <div id="userDropupMenu" class="dropdown-menu dropdown-menu-up dropdown-menu-full hidden" role="menu" aria-orientation="vertical" aria-labelledby="userProfileTrigger">
                        <div class="dropdown-header">
                            <div class="user-avatar-circle avatar-sm {{ auth()->user()?->avatar_url ? 'has-image' : '' }}">
                                @if (auth()->user()?->avatar_url)
                                    <img src="{{ auth()->user()->avatar_url }}" alt="{{ auth()->user()->name }}">
                                @else
                                    {{ strtoupper(substr(auth()->user()->name ?? 'SA', 0, 2)) }}
                                @endif
                            </div>
                            <div class="user-meta-info">
                                <span class="user-name-text">{{ auth()->user()->name }}</span>
                                <span class="brand-subtitle">{{ auth()->user()?->isSuperAdmin() ? 'Super Administrator' : match(auth()->user()?->user_type) { 'admin' => 'Administrator Sekolah', 'guru' => 'Tenaga Pendidik', 'siswa' => 'Peserta Didik', default => 'Pengguna Sistem' } }}</span>
                            </div>
                        </div>
                        <div class="dropdown-divider"></div>
                        <div class="dropdown-list">
                            <a href="{{ route('profile.index') }}" class="dropdown-item" role="menuitem">
                                <x-icon name="user" size="16" />
                                <span>Pengaturan Profile</span>
                            </a>
                            <a href="{{ route('profile.index', ['tab' => 'security']) }}" class="dropdown-item" role="menuitem">
                                <x-icon name="lock" size="16" />
                                <span>Ubah Password</span>
                            </a>
                            <a href="#bantuan" class="dropdown-item" role="menuitem" onclick="event.preventDefault(); window.showToast ? window.showToast('Pusat Bantuan INFORA segera hadir.') : null;">
                                <x-icon name="help-circle" size="16" />
                                <span>Bantuan</span>
                            </a>
                        </div>
                        <div class="dropdown-divider"></div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="dropdown-item is-danger" role="menuitem">
                                <x-icon name="log-out" size="16" />
                                <span>Keluar</span>
                            </button>
                        </form>
                    </div>

                    <!-- User Section Button Trigger -->
                    <button type="button" class="user-card-button" id="userProfileTrigger" aria-haspopup="true" aria-expanded="false" title="Menu Pengguna">
                        <div class="user-avatar-circle {{ auth()->user()?->avatar_url ? 'has-image' : '' }}">
                            @if (auth()->user()?->avatar_url)
                                <img src="{{ auth()->user()->avatar_url }}" alt="{{ auth()->user()->name }}">
                            @else
                                {{ strtoupper(substr(auth()->user()->name ?? 'SA', 0, 2)) }}
                            @endif
                        </div>
                        <div class="user-meta-info">
                            <span class="user-name-text">{{ auth()->user()->name }}</span>
                            <span class="brand-subtitle">@<span>{{ auth()->user()->username ?? 'superadmin' }}</span></span>
                        </div>
                        <x-icon name="chevron-up" size="16" class="chevron-icon" />
                    </button>
                </div>
            </div>
        </aside>

        <!-- Main Content Area -->
        <div class="layout-content-wrapper">
            <!-- Topbar Header -->
            <header class="topbar-header">
                <div class="topbar-left">
                    <button type="button" id="sidebarToggle" class="btn-icon" aria-label="Buka / Tutup Sidebar" title="Buka / Tutup Sidebar">
                        <x-icon name="sidebar-close" class="icon-toggle-sidebar" size="20" />
                    </button>
                    <span class="badge badge-cyan">{{ auth()->user()?->isSuperAdmin() ? 'Entitas Pengembang Platform' : (auth()->user()?->school?->name ?? 'Administrator Sekolah') }}</span>
                </div>
            </header>

            <!-- Main Body -->
            <main class="layout-main-body">
                <div class="layout-content-area">
                    @yield('content')
                </div>
            </main>
            <!-- Content Footer -->
            <footer class="content-footer">
                <div class="content-footer-left">
                    <span>&copy; {{ date('Y') }} <strong>INFORA</strong> &mdash; Era Baru Sistem Informasi Sekolah Menengah</span>
                </div>
                <div class="content-footer-right">
                    <span class="content-footer-badge">
                        <span class="status-dot"></span>
                        v1.0.0
                    </span>
                    <span class="content-footer-sep">&bull;</span>
                    <span class="content-footer-desc">SIM Sekolah</span>
                </div>
            </footer>
        </div>
    </div>

    <!-- Universal Progress Modal -->
    <x-progress-modal />
</body>
</html>
