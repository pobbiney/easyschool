<?php

namespace App\Http\Middleware;

use App\Services\TeacherAccessService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTeacher
{
    public function __construct(private TeacherAccessService $teacherAccess) {}

    public function handle(Request $request, Closure $next): Response
    {
        if (! auth()->check()) {
            return redirect()->route('admin-login')->with('message_error', 'Please log in to continue.');
        }

        if (! $this->teacherAccess->isTeacher()) {
            abort(403, 'This area is only available to teachers with an active login.');
        }

        if (! $this->teacherAccess->staffId()) {
            return redirect()->route('dashboard')->with('message_error', 'Your account is not linked to a staff profile. Contact an administrator.');
        }

        return $next($request);
    }
}
