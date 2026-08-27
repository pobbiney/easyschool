<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->redirectGuestsTo(function () {
            if (request()->is('super-admin') || request()->is('super-admin/*')) {
                return route('super-admin.login');
            }

            if (request()->is('parent') || request()->is('parent/*')) {
                return route('parent.login');
            }

            return route('admin-login');
        });

        $middleware->alias([
            'teacher' => \App\Http\Middleware\EnsureTeacher::class,
            'teacher.owns.class' => \App\Http\Middleware\EnsureTeacherOwnsClass::class,
            'parent' => \App\Http\Middleware\EnsureParent::class,
            'parent.owns.student' => \App\Http\Middleware\EnsureParentOwnsStudent::class,
            'school.tenant' => \App\Http\Middleware\EnsureSchoolTenant::class,
            'staff.auth' => \App\Http\Middleware\EnsureStaffAuthenticated::class,
            'parent.school' => \App\Http\Middleware\EnsureParentSchoolTenant::class,
            'super.admin' => \App\Http\Middleware\EnsureSuperAdmin::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
