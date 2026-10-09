<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCampusCareAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = session('campuscare_user', []);
        $role = strtoupper($user['role'] ?? '');

        if (! session()->has('campuscare_token') || $role !== 'ADMIN') {
            abort(403, 'Akses hanya untuk administrator.');
        }

        return $next($request);
    }
}
