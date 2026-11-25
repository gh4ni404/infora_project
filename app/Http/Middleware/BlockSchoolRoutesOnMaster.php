<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BlockSchoolRoutesOnMaster
{
    /**
     * Blokir rute operasional sekolah dengan 404 saat aplikasi berjalan dalam mode master.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (config('infora.node_role') === 'master') {
            $patterns = config('infora.school_only_route_patterns', []);

            if (! empty($patterns) && $request->routeIs($patterns)) {
                abort(404);
            }
        }

        return $next($request);
    }
}
