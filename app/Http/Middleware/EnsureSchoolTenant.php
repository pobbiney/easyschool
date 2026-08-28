<?php

namespace App\Http\Middleware;

use App\Models\School;
use App\Models\UsrUserLog;
use App\Services\Subscription\SchoolSubscriptionService;
use App\Support\SchoolAdminCategory;
use App\Support\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
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

        if (! TenantContext::isSuperAdminViewing() && $school->isSuspendedByAdmin()) {
            auth()->logout();
            TenantContext::clear();

            return redirect()->route('admin-login')
                ->with('login_error_message', "This school's account is suspended. Contact support.");
        }

        if (! TenantContext::isSuperAdminViewing()) {
            app(SchoolSubscriptionService::class)->suspendIfSubscriptionExpired($school);
            $school->refresh();
        }

        if (! TenantContext::isSuperAdminViewing() && ($school->isSubscriptionExpired() || $school->isSuspendedForSubscription())) {
            $user = auth()->user();
            $isAdmin = $user && SchoolAdminCategory::userIsAdmin($user, $school);
            $code = $school->code;

            $this->closeStaffSession($request);

            if ($isAdmin) {
                return redirect()->route('admin-login')
                    ->with('login_error_message', 'Your school subscription has ended. Renew to sign in.')
                    ->with('subscription_renew_url', route('renew-subscription', ['school_code' => $code]));
            }

            return redirect()->route('admin-login')
                ->with('login_error_message', 'Subscription has ended. Ask your school administrator to renew.');
        }

        if (! $school->isApproved()) {
            auth()->logout();
            TenantContext::clear();

            return redirect()->route('admin-login')
                ->with('login_error_message', 'Your school account is not active.');
        }

        return $next($request);
    }

    private function closeStaffSession(Request $request): void
    {
        $logId = (int) $request->session()->get('userLogId');
        if ($logId > 0) {
            $log = UsrUserLog::query()->find($logId);
            if ($log) {
                $log->logout_date = Carbon::now();
                $log->update();
            }
        }

        $request->session()->forget('userLogId');
        auth()->logout();
        TenantContext::clear();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }
}
