<?php

namespace App\Http\Middleware;

use App\Models\TenantNode;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyTenantNodeSignature
{
    /**
     * Verifikasi identitas node sekolah menggunakan Bearer token dan HMAC-SHA256.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (config('infora.node_role') !== 'master') {
            abort(404);
        }

        $nodeCode = trim((string) $request->header('X-Node-Code', ''));
        $token = trim((string) $request->bearerToken());
        $timestamp = $request->header('X-Timestamp');
        $signature = trim((string) $request->header('X-Signature', ''));

        if ($nodeCode === '' || $token === '' || ! is_numeric($timestamp) || $signature === '') {
            return response()->json([
                'message' => 'Header autentikasi node tidak lengkap.',
            ], 401);
        }

        $skewSeconds = (int) config('infora.heartbeat.clock_skew_seconds', 300);
        if (abs(now()->timestamp - (int) $timestamp) > $skewSeconds) {
            return response()->json([
                'message' => 'Stempel waktu request kedaluwarsa.',
            ], 401);
        }

        $tokenHash = hash('sha256', $token);
        $rawBody = $request->getContent();
        $signedPayload = ((string) $timestamp).'.'.$rawBody;

        $matchedNode = TenantNode::query()
            ->where('node_code', $nodeCode)
            ->where('is_active', true)
            ->get()
            ->first(function (TenantNode $node) use ($tokenHash, $signedPayload, $signature): bool {
                if (! hash_equals((string) $node->api_token_hash, $tokenHash)) {
                    return false;
                }

                $expectedSignature = hash_hmac('sha256', $signedPayload, (string) $node->hmac_secret);

                return hash_equals($expectedSignature, $signature);
            });

        if (! $matchedNode) {
            return response()->json([
                'message' => 'Kredensial atau tanda tangan node sekolah tidak valid.',
            ], 401);
        }

        $request->attributes->set('tenant_node', $matchedNode);

        return $next($request);
    }
}
