<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureMasterNode
{
    /**
     * Pastikan rute khusus portal Master hanya aktif saat aplikasi berjalan pada mode master.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (config('infora.node_role') !== 'master') {
            abort(404);
        }

        return $next($request);
    }
}
