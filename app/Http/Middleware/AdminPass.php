<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminPass
{
    public function handle(Request $request, Closure $next)
    {
        $role = $request->user()->role;

        // Admin uniquement
        if ($role === 'admin') {
            return $next($request);
        }

        abort(403);
    }
}
