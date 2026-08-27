<?php

namespace App\Http\Middleware;

use App\Support\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureStaffAuthenticated
{
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check()) {
            return $next($request);
        }

        if (auth('super_admin')->check() && TenantContext::isSuperAdminViewing() && TenantContext::schoolId()) {
            return $next($request);
        }

        return redirect()->guest(route('admin-login'));
    }
}
