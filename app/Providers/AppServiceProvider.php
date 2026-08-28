<?php

namespace App\Providers;

use App\Models\School;
use App\Services\Subscription\SchoolSubscriptionService;
use App\Support\AcademicPeriodDefaults;
use App\Support\SchoolAdminCategory;
use App\Support\TenantContext;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('*', function ($view) {
            $emptyDefaults = [
                'defaultAcademicYearId' => null,
                'defaultAcademicTermId' => null,
                'defaultAcademicYearName' => null,
                'defaultAcademicTermName' => null,
            ];

            try {
                if (! Schema::hasColumn('school_settings', 'default_academic_year_id')) {
                    $view->with($emptyDefaults);

                    return;
                }

                $defaults = AcademicPeriodDefaults::forFrontend();

                $view->with('defaultAcademicYearId', $defaults['year_id']);
                $view->with('defaultAcademicTermId', $defaults['term_id']);
                $view->with('defaultAcademicYearName', $defaults['year_name']);
                $view->with('defaultAcademicTermName', $defaults['term_name']);
            } catch (\Throwable $e) {
                $view->with($emptyDefaults);
            }
        });

        View::composer('layouts.app', function ($view) {
            $view->with('subscriptionNotice', $this->subscriptionNoticeForLayout());
        });
    }

    /**
     * @return array<string, mixed>|null
     */
    private function subscriptionNoticeForLayout(): ?array
    {
        try {
            $schoolId = TenantContext::schoolId();

            if (! $schoolId) {
                return null;
            }

            $school = School::query()->find($schoolId);

            if (! $school) {
                return null;
            }

            $notice = app(SchoolSubscriptionService::class)->subscriptionNotice($school);

            if (! $notice) {
                return null;
            }

            $user = auth()->user();
            $canRenew = ($user && SchoolAdminCategory::userIsAdmin($user, $school))
                || auth('super_admin')->check();

            $notice['can_renew'] = $canRenew;
            $notice['renew_url'] = ($canRenew && $school->code)
                ? route('renew-subscription', ['school_code' => $school->code])
                : null;

            return $notice;
        } catch (\Throwable $e) {
            return null;
        }
    }
}
