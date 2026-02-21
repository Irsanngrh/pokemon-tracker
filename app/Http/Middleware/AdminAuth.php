<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->query('key') !== env('ADMIN_KEY', 'memekopedia')) {
            abort(403, 'Akses Ditolak. Kunci Admin Salah.');
        }

        return $next($request);
    }
}