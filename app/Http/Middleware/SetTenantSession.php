<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class SetTenantSession
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            session(['tenant_id' => Auth::user()->tenant_id]);
        }

        return $next($request);
    }
}
