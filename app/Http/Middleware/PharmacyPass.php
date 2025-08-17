<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class PharmacyPass
{
    public function handle(Request $request, Closure $next)
    {
        $role = $request->user()->role;

        // Pharmacy a ses droits + ceux des niveaux supérieurs (admin)
        if (in_array($role, ['pharmacy', 'admin'])) {
            return $next($request);
        }

        abort(403);
    }
}
