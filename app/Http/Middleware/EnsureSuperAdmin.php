<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSuperAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! auth('super_admin')->check()) {
            return redirect()->route('super-admin.login');
        }

        $admin = auth('super_admin')->user();

        if (! $admin->isActive()) {
            auth('super_admin')->logout();

            return redirect()->route('super-admin.login')
                ->with('login_error_message', 'Your super admin account is inactive.');
        }

        return $next($request);
    }
}
