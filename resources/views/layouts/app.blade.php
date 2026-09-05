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
                <a href="{{ route('dashboard') }}" class="sidebar-brand-link" title="INFORA - Platform Governance Core">
                    <img src="{{ asset('images/infora-emblem-badge.png') }}" alt="INFORA Logo" class="brand-badge-img">
                    <div class="sidebar-brand-text">
                        <h1 class="brand-title">INFORA</h1>
                        <div class="brand-subtitle">Platform Governance Core</div>
                    </div>
                </a>
            </div>

            <div class="sidebar-search">
                <div class="search-box">
                    <svg class="search-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
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
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <line x1="18" y1="6" x2="6" y2="18"></line>
                            <line x1="6" y1="6" x2="18" y2="18"></line>
                        </svg>
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
                                    $hasSubMenus = $menu->subMenus->isNotEmpty();
                                    $isMenuActive = false;

                                    if ($hasSubMenus) {
                                        foreach ($menu->subMenus as $sub) {
                                            if ($sub->route_name && Route::has($sub->route_name)) {
                                                $basePattern = str_ends_with($sub->route_name, '.index')
                                                    ? substr($sub->route_name, 0, -6) . '.*'
                                                    : $sub->route_name;
                                                if (request()->routeIs($sub->route_name) || request()->routeIs($basePattern)) {
                                                    $isMenuActive = true;
                                                    break;
                                                }
                                            }
                                        }
                                    } else {
                                        $basePattern = str_ends_with($menu->route_name ?? '', '.index')
                                            ? substr($menu->route_name, 0, -6) . '.*'
                                            : $menu->route_name;
                                        $isMenuActive = $menu->route_name && Route::has($menu->route_name) && (request()->routeIs($menu->route_name) || request()->routeIs($basePattern));
                                    }
                                @endphp

                                @if ($hasSubMenus)
                                    <div class="nav-group-item {{ $isMenuActive ? 'is-open active-parent' : '' }}" data-menu-id="{{ $menu->id }}">
                                        <button type="button" class="nav-group-trigger {{ $isMenuActive ? 'active' : '' }}" aria-expanded="{{ $isMenuActive ? 'true' : 'false' }}" title="{{ $menu->name }}">
                                            <x-icon :name="$menu->icon" class="nav-item-icon" />
                                            <span class="nav-item-title">{{ $menu->name }}</span>
                                            <span class="nav-count-badge">{{ $menu->subMenus->count() }}</span>
                                            <svg class="nav-arrow-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                <polyline points="6 9 12 15 18 9"></polyline>
                                            </svg>
                                        </button>
                                        <div class="nav-submenu-list">
                                            @foreach ($menu->subMenus as $subMenu)
                                                @php
                                                    $basePattern = str_ends_with($subMenu->route_name ?? '', '.index')
                                                        ? substr($subMenu->route_name, 0, -6) . '.*'
                                                        : $subMenu->route_name;
                                                    $isSubActive = $subMenu->route_name && Route::has($subMenu->route_name) && (request()->routeIs($subMenu->route_name) || request()->routeIs($basePattern));
                                                    $subHref = ($subMenu->route_name && Route::has($subMenu->route_name)) ? route($subMenu->route_name) : '#';
                                                @endphp
                                                <a href="{{ $subHref }}" class="nav-submenu-item {{ $isSubActive ? 'active' : '' }}" title="{{ $subMenu->name }}">
                                                    <span class="nav-submenu-bullet"></span>
                                                    <span class="nav-submenu-text">{{ $subMenu->name }}</span>
                                                </a>
                                            @endforeach
                                        </div>
                                    </div>
                                @else
                                    @php
                                        $menuHref = ($menu->route_name && Route::has($menu->route_name)) ? route($menu->route_name) : '#';
                                    @endphp
                                    <a href="{{ $menuHref }}" class="nav-link-item {{ $isMenuActive ? 'active' : '' }}" title="{{ $menu->name }}">
                                        <x-icon :name="$menu->icon" class="nav-item-icon" />
                                        <span>{{ $menu->name }}</span>
                                    </a>
                                @endif
                            @endforeach
                        @endif
                    @endforeach
                @else
                    <div class="menu-category-label">Menu Utama</div>
                    <a href="{{ route('dashboard') }}" class="nav-link-item active" title="Dashboard">
                        <x-icon name="layout-dashboard" class="nav-item-icon" />
                        <span>Dashboard</span>
                    </a>
                @endif

                <div id="menuSearchEmpty" class="empty-state hidden">
                    <svg class="empty-state-icon" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                        <line x1="8" y1="11" x2="14" y2="11"></line>
                    </svg>
                    <span class="empty-state-text">Menu tidak ditemukan</span>
                </div>
            </nav>
        </aside>

        <!-- Main Content Area -->
        <div class="layout-content-wrapper">
            <!-- Topbar Header -->
            <header class="topbar-header">
                <div class="topbar-left">
                    <button type="button" id="sidebarToggle" class="btn-icon" aria-label="Buka / Tutup Sidebar" title="Buka / Tutup Sidebar">
                        <svg class="icon-toggle-sidebar" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <rect width="18" height="18" x="3" y="3" rx="2"></rect>
                            <path d="M9 3v18"></path>
                            <path class="toggle-arrow" d="m14 9-3 3 3 3"></path>
                        </svg>
                    </button>
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
