<?php

namespace App\Http\Middleware;

use App\Models\School;
use App\Support\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureParentSchoolTenant
{
    public function handle(Request $request, Closure $next): Response
    {
        $schoolId = TenantContext::schoolId();

        if (! $schoolId) {
            return redirect()->route('parent.login')
                ->with('login_error_message', 'Please sign in with your school code.');
        }

        $school = School::query()->find($schoolId);

        if (! $school) {
            auth('parent')->logout();
            TenantContext::clear();

            return redirect()->route('parent.login')
                ->with('login_error_message', 'Your school portal is not available.');
        }

        if ($school->isSuspended()) {
            auth('parent')->logout();
            TenantContext::clear();

            return redirect()->route('parent.login')
                ->with('login_error_message', "This school's account is suspended. Contact support.");
        }

        if (! $school->isApproved()) {
            auth('parent')->logout();
            TenantContext::clear();

            return redirect()->route('parent.login')
                ->with('login_error_message', 'Your school portal is not available.');
        }

        return $next($request);
    }
}
