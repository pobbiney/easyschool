<?php

namespace App\Http\Middleware;

use App\Models\School;
use App\Support\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSchoolTenant
{
    public function handle(Request $request, Closure $next): Response
    {
        if (auth('super_admin')->check() && TenantContext::isSuperAdminViewing()) {
            return $next($request);
        }

        $schoolId = TenantContext::schoolId();

        if (! $schoolId) {
            return redirect()->route('admin-login')
                ->with('login_error_message', 'Please sign in with your school code.');
        }

        $school = School::query()->find($schoolId);

        if (! $school) {
            auth()->logout();
            TenantContext::clear();

            return redirect()->route('admin-login')
                ->with('login_error_message', 'Your school account is not active.');
        }

        if ($school->isSuspended() && ! TenantContext::isSuperAdminViewing()) {
            auth()->logout();
            TenantContext::clear();

            return redirect()->route('admin-login')
                ->with('login_error_message', "This school's account is suspended. Contact support.");
        }

        if (! $school->isApproved()) {
            auth()->logout();
            TenantContext::clear();

            return redirect()->route('admin-login')
                ->with('login_error_message', 'Your school account is not active.');
        }

        return $next($request);
    }
}
