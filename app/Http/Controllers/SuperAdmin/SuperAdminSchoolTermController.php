<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\AcademicTerm;
use App\Models\AcademicTermCalendar;
use App\Models\AcademicYear;
use App\Models\School;
use App\Models\SchoolSetting;
use App\Services\Subscription\SchoolSubscriptionService;
use App\Services\Tenant\SchoolActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class SuperAdminSchoolTermController extends Controller
{
    public function show(School $school, SchoolSubscriptionService $subscriptions)
    {
        $settings = SchoolSetting::query()
            ->withoutGlobalScopes()
            ->where('school_id', $school->id)
            ->first();

        $academicYears = AcademicYear::query()
            ->withoutGlobalScopes()
            ->where('school_id', $school->id)
            ->where('status', 'Active')
            ->orderByDesc('name')
            ->get();

        $academicTerms = AcademicTerm::query()
            ->withoutGlobalScopes()
            ->where('school_id', $school->id)
            ->where('status', 'Active')
            ->orderBy('sort_order')
            ->get();

        $termCalendars = AcademicTermCalendar::query()
            ->withoutGlobalScopes()
            ->where('school_id', $school->id)
            ->get()
            ->mapWithKeys(function (AcademicTermCalendar $calendar) {
                return [
                    $calendar->academic_year_id.':'.$calendar->academic_term_id => [
                        'opening_date' => $calendar->opening_date?->format('Y-m-d'),
                        'vacation_date' => $calendar->vacation_date?->format('Y-m-d'),
                    ],
                ];
            });

        $currentCalendar = $subscriptions->currentCalendar($school);

        $payments = $school->subscriptionPayments()
            ->with('subscription')
            ->latest('id')
            ->get();

        return view('super-admin.schools.subscriptions', [
            'school' => $school->fresh(),
            'settings' => $settings,
            'academicYears' => $academicYears,
            'academicTerms' => $academicTerms,
            'termCalendars' => $termCalendars,
            'currentCalendar' => $currentCalendar,
            'payments' => $payments,
        ]);
    }

    public function updateDates(
        Request $request,
        School $school,
        SchoolSubscriptionService $subscriptions,
        SchoolActivityLogger $logger,
    ) {
        $yearIds = AcademicYear::query()
            ->withoutGlobalScopes()
            ->where('school_id', $school->id)
            ->pluck('id')
            ->all();

        $termIds = AcademicTerm::query()
            ->withoutGlobalScopes()
            ->where('school_id', $school->id)
            ->pluck('id')
            ->all();

        $validated = $request->validate([
            'academic_year_id' => ['required', 'integer', Rule::in($yearIds)],
            'academic_term_id' => ['required', 'integer', Rule::in($termIds)],
            'opening_date' => 'required|date',
            'vacation_date' => 'required|date|after_or_equal:opening_date',
        ], [
            'vacation_date.after_or_equal' => 'The vacation date must be on or after the opening date.',
        ]);

        $subscriptions->updateTermDates(
            $school,
            (int) $validated['academic_year_id'],
            (int) $validated['academic_term_id'],
            $validated['opening_date'],
            $validated['vacation_date'],
        );

        $logger->log(
            action: 'school.term_dates_updated',
            description: 'Opening and vacation dates updated by super admin',
            payload: [
                'school_id' => $school->id,
                'academic_year_id' => (int) $validated['academic_year_id'],
                'academic_term_id' => (int) $validated['academic_term_id'],
                'opening_date' => $validated['opening_date'],
                'vacation_date' => $validated['vacation_date'],
            ],
            schoolId: $school->id,
            schoolCode: $school->code,
            actorType: 'super_admin',
            actorId: Auth::guard('super_admin')->id(),
        );

        return redirect()
            ->route('super-admin.schools.subscriptions', $school)
            ->with('message_success', "Term dates updated for {$school->name}.");
    }
}
