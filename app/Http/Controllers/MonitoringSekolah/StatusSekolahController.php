<?php

namespace App\Http\Controllers\MonitoringSekolah;

use App\Http\Controllers\Controller;
use App\Http\Requests\MonitoringSekolah\StoreTenantNodeRequest;
use App\Http\Requests\MonitoringSekolah\UpdateTenantNodeRequest;
use App\Models\TenantNode;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\View\View;
use Throwable;

class StatusSekolahController extends Controller
{
    /**
     * Tampilkan daftar node sekolah serta ringkasan perkembangan modul, menu, dan user.
     */
    public function index(Request $request): View
    {
        $search = (string) $request->query('search', '');
        $statusFilter = (string) $request->query('status', '');

        $allNodes = TenantNode::query()->with('snapshot')->orderBy('school_name')->get();
        $summaryStats = $this->buildStatusSummary($allNodes);

        $filtered = $allNodes->filter(function (TenantNode $node) use ($search, $statusFilter): bool {
            if ($search !== '') {
                $needle = mb_strtolower(trim($search));
                $haystack = mb_strtolower($node->school_name.' '.$node->node_code.' '.($node->npsn ?? ''));
                if (! str_contains($haystack, $needle)) {
                    return false;
                }
            }

            if ($statusFilter !== '' && $node->computed_status !== $statusFilter) {
                return false;
            }

            return true;
        })->values();

        return view('monitoring-sekolah.status-sekolah.index', compact(
            'filtered',
            'summaryStats',
            'search',
            'statusFilter'
        ));
    }

    /**
     * Daftarkan node sekolah baru beserta kredensial autentikasi API-nya.
     */
    public function store(StoreTenantNodeRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $credentials = TenantNode::generateCredentials();

        $node = TenantNode::create([
            'node_code' => strtolower(trim((string) $data['node_code'])),
            'school_name' => $data['school_name'],
            'npsn' => $data['npsn'] ?? null,
            'base_url' => ! empty($data['base_url']) ? rtrim((string) $data['base_url'], '/') : null,
            'api_token_hash' => $credentials['token_hash'],
            'hmac_secret' => $credentials['hmac_secret'],
            'is_active' => array_key_exists('is_active', $data) ? (bool) $data['is_active'] : true,
            'status' => 'pending',
            'db_status' => 'unknown',
            'policy_version' => 1,
        ]);

        return redirect()
            ->route('monitoring-sekolah.status-sekolah.show', $node)
            ->with('success', 'Node sekolah berhasil didaftarkan. Salin kredensial di bawah ke file .env server sekolah.')
            ->with('generated_credentials', [
                'node_code' => $node->node_code,
                'node_token' => $credentials['plain_token'],
                'node_secret' => $credentials['hmac_secret'],
            ]);
    }

    /**
     * Tampilkan detail perkembangan modul, menu, sub-menu, dan user milik satu sekolah.
     */
    public function show(TenantNode $tenantNode): View
    {
        $tenantNode->load(['snapshot', 'menuPolicies']);

        return view('monitoring-sekolah.status-sekolah.show', compact('tenantNode'));
    }

    /**
     * Perbarui identitas dan URL node sekolah.
     */
    public function update(UpdateTenantNodeRequest $request, TenantNode $tenantNode): RedirectResponse
    {
        $data = $request->validated();

        $tenantNode->update([
            'node_code' => strtolower(trim((string) $data['node_code'])),
            'school_name' => $data['school_name'],
            'npsn' => $data['npsn'] ?? null,
            'base_url' => ! empty($data['base_url']) ? rtrim((string) $data['base_url'], '/') : null,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()
            ->route('monitoring-sekolah.status-sekolah.show', $tenantNode)
            ->with('success', 'Data node sekolah berhasil diperbarui.');
    }

    /**
     * Aktifkan atau nonaktifkan node sekolah dari kontrol pusat Master.
     */
    public function toggleStatus(TenantNode $tenantNode): RedirectResponse
    {
        $tenantNode->update([
            'is_active' => ! $tenantNode->is_active,
        ]);

        $label = $tenantNode->is_active ? 'diaktifkan kembali' : 'dinonaktifkan';

        return redirect()
            ->back()
            ->with('success', "Node sekolah {$tenantNode->school_name} berhasil {$label}.");
    }

    /**
     * Buat ulang token Bearer dan HMAC secret untuk node sekolah.
     */
    public function regenerateCredentials(TenantNode $tenantNode): RedirectResponse
    {
        $credentials = TenantNode::generateCredentials();

        $tenantNode->update([
            'api_token_hash' => $credentials['token_hash'],
            'hmac_secret' => $credentials['hmac_secret'],
        ]);

        return redirect()
            ->route('monitoring-sekolah.status-sekolah.show', $tenantNode)
            ->with('success', 'Kredensial node berhasil dibuat ulang. Perbarui .env pada server sekolah.')
            ->with('generated_credentials', [
                'node_code' => $tenantNode->node_code,
                'node_token' => $credentials['plain_token'],
                'node_secret' => $credentials['hmac_secret'],
            ]);
    }

    /**
     * Lakukan active probe HTTP ke endpoint /up milik server sekolah secara langsung.
     */
    public function ping(TenantNode $tenantNode): RedirectResponse
    {
        if (blank($tenantNode->base_url)) {
            return redirect()
                ->back()
                ->with('error', 'URL aplikasi sekolah belum diisi pada node ini.');
        }

        $targetUrl = rtrim((string) $tenantNode->base_url, '/').'/up';

        try {
            $start = hrtime(true);
            $response = Http::timeout((int) config('infora.heartbeat.timeout_seconds', 8))->get($targetUrl);
            $latencyMs = (int) round((hrtime(true) - $start) / 1_000_000);

            if ($response->successful()) {
                return redirect()
                    ->back()
                    ->with('success', "Server {$tenantNode->school_name} merespons HTTP {$response->status()} ({$latencyMs} ms).");
            }

            return redirect()
                ->back()
                ->with('error', "Server {$tenantNode->school_name} merespons status HTTP {$response->status()}.");
        } catch (Throwable $e) {
            return redirect()
                ->back()
                ->with('error', "Gagal menghubungi {$targetUrl}: {$e->getMessage()}");
        }
    }

    /**
     * Hapus node sekolah beserta seluruh snapshot dan kebijakan menunya.
     */
    public function destroy(TenantNode $tenantNode): RedirectResponse
    {
        $schoolName = $tenantNode->school_name;
        $tenantNode->delete();

        return redirect()
            ->route('monitoring-sekolah.status-sekolah.index')
            ->with('success', "Node sekolah {$schoolName} berhasil dihapus dari pemantauan Master.");
    }

    /**
     * Sub-menu khusus untuk memantau indikator Online/Offline server & database seluruh sekolah.
     */
    public function serverStatus(Request $request): View
    {
        $statusFilter = (string) $request->query('status', '');
        $nodes = TenantNode::query()->with('snapshot')->orderByDesc('last_heartbeat_at')->orderBy('school_name')->get();
        $summaryStats = $this->buildStatusSummary($nodes);

        if ($statusFilter !== '') {
            $nodes = $nodes->filter(fn (TenantNode $node) => $node->computed_status === $statusFilter)->values();
        }

        return view('monitoring-sekolah.status-server.index', compact(
            'nodes',
            'summaryStats',
            'statusFilter'
        ));
    }

    /**
     * Hitung ringkasan jumlah node untuk kartu statistik atas.
     *
     * @param  Collection<int, TenantNode>  $nodes
     * @return array<string, int>
     */
    protected function buildStatusSummary(Collection $nodes): array
    {
        return [
            'total' => $nodes->count(),
            'online' => $nodes->filter(fn (TenantNode $n) => $n->computed_status === 'online')->count(),
            'idle' => $nodes->filter(fn (TenantNode $n) => $n->computed_status === 'idle')->count(),
            'degraded' => $nodes->filter(fn (TenantNode $n) => $n->computed_status === 'degraded')->count(),
            'offline' => $nodes->filter(fn (TenantNode $n) => $n->computed_status === 'offline')->count(),
        ];
    }
}
