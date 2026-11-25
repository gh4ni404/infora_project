@extends('layouts.app')

@section('content')
<x-page-header
    title="Atur Menu Admin Sekolah"
    subtitle="Tentukan modul, menu, dan sub-menu yang diizinkan untuk {{ $tenantNode->school_name }} agar dapat dibagikan oleh Admin ke user mereka"
>
    <x-slot:actions>
        <a href="{{ route('master-control.nodes.show', $tenantNode) }}" class="btn-secondary">
            <x-icon name="chevron-left" size="16" />
            <span>Kembali ke Detail</span>
        </a>
    </x-slot:actions>
</x-page-header>

<x-alert type="success" />
<x-alert type="error" />
<x-alert type="validation" />

<div class="card-surface">
    <div class="card-header">
        <div>
            <h2 class="user-name-text">{{ $tenantNode->school_name }} ({{ $tenantNode->node_code }})</h2>
            <div class="brand-subtitle">
                Versi Kebijakan Master: <strong>v{{ $tenantNode->policy_version }}</strong> &bull;
                Versi Terakhir Diterapkan Sekolah: <strong>v{{ $tenantNode->last_applied_policy_version }}</strong>
            </div>
        </div>
        <span class="badge {{ $tenantNode->computed_status_badge }}">{{ $tenantNode->computed_status_label }}</span>
    </div>

    <div class="card-body">
        <form method="POST" action="{{ route('master-control.nodes.menus.update', $tenantNode) }}">
            @csrf
            @method('PUT')

            <div class="matrix-container">
                @php $rowIndex = 0; @endphp
                @foreach ($modulesTree as $module)
                    <div class="matrix-module-card">
                        <div class="matrix-module-header">
                            <div class="matrix-module-title">
                                <x-icon name="layers" class="nav-item-icon" />
                                <span>{{ $module['name'] ?? '-' }}</span>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="matrix-table">
                                <thead>
                                    <tr>
                                        <th>Item Menu / Sub-Menu</th>
                                        <th>Kunci Sinkronisasi</th>
                                        <th class="matrix-th-center">Izinkan untuk Sekolah</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach (($module['menus'] ?? []) as $menu)
                                        @php
                                            $menuKey = $menu['sync_key'] ?? '';
                                            $menuPolicy = $existingPolicies->get('menu:'.$menuKey);
                                            $menuAllowed = $menuPolicy ? $menuPolicy->is_allowed : true;
                                            $idx = $rowIndex++;
                                        @endphp
                                        <tr class="matrix-row-menu">
                                            <td>
                                                <div class="table-cell-bold">{{ $menu['name'] ?? '-' }}</div>
                                                <input type="hidden" name="items[{{ $idx }}][target_type]" value="menu">
                                                <input type="hidden" name="items[{{ $idx }}][sync_key]" value="{{ $menuKey }}">
                                                <input type="hidden" name="items[{{ $idx }}][label_snapshot]" value="{{ $menu['name'] ?? '-' }}">
                                            </td>
                                            <td><code>{{ $menuKey }}</code></td>
                                            <td class="matrix-th-center">
                                                <input type="hidden" name="items[{{ $idx }}][is_allowed]" value="0">
                                                <input type="checkbox" name="items[{{ $idx }}][is_allowed]" value="1" class="form-check-input" @checked($menuAllowed)>
                                            </td>
                                        </tr>

                                        @foreach (($menu['sub_menus'] ?? []) as $sub)
                                            @php
                                                $subKey = $sub['sync_key'] ?? '';
                                                $subPolicy = $existingPolicies->get('sub_menu:'.$subKey);
                                                $subAllowed = $subPolicy ? $subPolicy->is_allowed : true;
                                                $subIdx = $rowIndex++;
                                            @endphp
                                            <tr>
                                                <td class="pl-8">
                                                    &rdsh; {{ $sub['name'] ?? '-' }}
                                                    <input type="hidden" name="items[{{ $subIdx }}][target_type]" value="sub_menu">
                                                    <input type="hidden" name="items[{{ $subIdx }}][sync_key]" value="{{ $subKey }}">
                                                    <input type="hidden" name="items[{{ $subIdx }}][label_snapshot]" value="{{ $sub['name'] ?? '-' }}">
                                                </td>
                                                <td><code>{{ $subKey }}</code></td>
                                                <td class="matrix-th-center">
                                                    <input type="hidden" name="items[{{ $subIdx }}][is_allowed]" value="0">
                                                    <input type="checkbox" name="items[{{ $subIdx }}][is_allowed]" value="1" class="form-check-input" @checked($subAllowed)>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="page-actions mt-6">
                <button type="submit" class="btn-primary">
                    <x-icon name="save" size="16" />
                    <span>Simpan & Kirim Update Kebijakan Menu</span>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
