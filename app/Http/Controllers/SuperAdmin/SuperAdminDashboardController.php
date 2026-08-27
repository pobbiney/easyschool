<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Models\SchoolActivityLog;
use App\Models\Staff;
use App\Models\Student;
use App\Models\User;
use App\Services\Tenant\SchoolActivityLogger;
use App\Services\Tenant\SchoolProvisioningService;
use App\Support\TenantContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SuperAdminDashboardController extends Controller
{
    public function index()
    {
        $schools = School::query()
            ->withCount([
                'settings',
            ])
            ->orderByDesc('created_at')
            ->get()
            ->map(function (School $school) {
                $school->students_count = Student::query()
                    ->withoutGlobalScopes()
                    ->where('school_id', $school->id)
                    ->count();
                $school->staff_count = Staff::query()
                    ->withoutGlobalScopes()
                    ->where('school_id', $school->id)
                    ->count();
                $school->users_count = User::query()
                    ->withoutGlobalScopes()
                    ->where('school_id', $school->id)
                    ->count();

                return $school;
            });

        $pendingCount = $schools->where('status', School::STATUS_PENDING)->count();
        $approvedCount = $schools->where('status', School::STATUS_APPROVED)->count();

        $recentActivity = SchoolActivityLog::query()
            ->latest('id')
            ->limit(20)
            ->get();

        return view('super-admin.dashboard', compact('schools', 'pendingCount', 'approvedCount', 'recentActivity'));
    }

    public function registrations()
    {
        $schools = School::query()
            ->where('status', School::STATUS_PENDING)
            ->orderBy('created_at')
            ->get();

        return view('super-admin.registrations', compact('schools'));
    }

    public function approve(School $school, SchoolProvisioningService $provisioning)
    {
        $school = $provisioning->approve($school, Auth::guard('super_admin')->user());

        return redirect()
            ->route('super-admin.registrations')
            ->with('message_success', "Approved {$school->name}. School code: {$school->code}");
    }

    public function reject(Request $request, School $school, SchoolProvisioningService $provisioning)
    {
        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:1000',
        ]);

        $provisioning->reject($school, Auth::guard('super_admin')->user(), $validated['rejection_reason']);

        return redirect()
            ->route('super-admin.registrations')
            ->with('message_success', 'Registration rejected.');
    }

    public function enterSchool(School $school)
    {
        if (! $school->isApproved() && ! $school->isSuspended()) {
            return back()->with('message_error', 'Only approved or suspended schools can be entered.');
        }

        TenantContext::setSchool($school, true);

        return redirect()
            ->route('dashboard')
            ->with('message_success', "Viewing {$school->displayLabel()}");
    }

    public function exitSchool()
    {
        TenantContext::clear();

        return redirect()->route('super-admin.dashboard');
    }

    public function suspend(School $school, SchoolActivityLogger $logger)
    {
        $school->update(['status' => School::STATUS_SUSPENDED]);

        $logger->log(
            action: 'school.suspended',
            description: 'School suspended by super admin',
            payload: ['school_id' => $school->id],
            schoolId: $school->id,
            schoolCode: $school->code,
            actorType: 'super_admin',
            actorId: Auth::guard('super_admin')->id(),
        );

        return back()->with('message_success', "{$school->name} has been suspended.");
    }

    public function reactivate(School $school, SchoolActivityLogger $logger)
    {
        $school->update(['status' => School::STATUS_APPROVED]);

        $logger->log(
            action: 'school.reactivated',
            description: 'School reactivated by super admin',
            payload: ['school_id' => $school->id],
            schoolId: $school->id,
            schoolCode: $school->code,
            actorType: 'super_admin',
            actorId: Auth::guard('super_admin')->id(),
        );

        return back()->with('message_success', "{$school->name} is active again.");
    }

    public function activity()
    {
        $logs = SchoolActivityLog::query()
            ->latest('id')
            ->paginate(50);

        return view('super-admin.activity', compact('logs'));
    }
}
