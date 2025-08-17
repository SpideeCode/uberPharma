<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CourierPass
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->user() && $request->user()->role === 'courier') {
            return $next($request);
        }

        abort(403, 'Accès interdit');
    }
}
