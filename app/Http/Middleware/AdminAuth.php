<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminAuth
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->query('key') !== env('ADMIN_SECRET', 'admin123')) {
            abort(403);
        }

        return $next($request);
    }
}