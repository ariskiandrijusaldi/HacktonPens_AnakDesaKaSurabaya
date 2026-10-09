<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCampusCareOfficer
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! session()->has('campuscare_token')) {
            return redirect()->route('masuk');
        }

        return $next($request);
    }
}
