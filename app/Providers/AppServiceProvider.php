<?php

namespace App\Providers;

use App\Support\AcademicPeriodDefaults;
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
    }
}
