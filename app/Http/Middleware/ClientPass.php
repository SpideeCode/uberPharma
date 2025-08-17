<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ClientPass
{
    public function handle(Request $request, Closure $next)
    {
        $role = $request->user()->role;

        // Client a ses droits + ceux des niveaux supérieurs (pharmacy, admin)
        if (in_array($role, ['client', 'pharmacy', 'admin'])) {
            return $next($request);
        }

        abort(403);
    }
}
