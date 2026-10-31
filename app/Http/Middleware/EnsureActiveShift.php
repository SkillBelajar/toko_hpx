<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureActiveShift
{
    /**
     * Tenant tidak boleh masuk POS tanpa shift terbuka.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user?->activeShift()) {
            return redirect()->route('tenant.shift.open')
                ->with('error', 'Buka shift dulu sebelum masuk ke kasir.');
        }

        return $next($request);
    }
}
