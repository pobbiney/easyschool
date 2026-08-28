<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\AcademicTerm;
use App\Models\AcademicTermCalendar;
use App\Models\AcademicYear;
use App\Models\School;
use App\Models\SchoolSetting;
use App\Models\UsrUserLog;
use App\Services\Subscription\SchoolSubscriptionService;
use App\Support\SchoolAdminCategory;
use App\Support\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class SchoolSettingController extends Controller
{
    public function index()
    {
        $school = SchoolSetting::current();
        $termCalendars = AcademicTermCalendar::query()
            ->get()
            ->mapWithKeys(function (AcademicTermCalendar $calendar) {
                $complete = $calendar->opening_date && $calendar->vacation_date;

                return [
                    $calendar->academic_year_id.':'.$calendar->academic_term_id => [
                        'opening_date' => $calendar->opening_date?->format('Y-m-d'),
                        'vacation_date' => $calendar->vacation_date?->format('Y-m-d'),
                        'locked' => $complete,
                    ],
                ];
            });

        $currentCalendar = $school->currentTermCalendar();
        $selectedYearId = old('default_academic_year_id', $currentCalendar?->academic_year_id ?? $school->default_academic_year_id);
        $selectedTermId = old('default_academic_term_id', $currentCalendar?->academic_term_id ?? $school->default_academic_term_id);

        return view('settings.school-settings', [
            'school' => $school,
            'academicYears' => AcademicYear::where('status', 'Active')->orderBy('name', 'desc')->get(),
            'academicTerms' => AcademicTerm::where('status', 'Active')->orderBy('sort_order')->get(),
            'termCalendars' => $termCalendars,
            'currentCalendar' => $currentCalendar,
            'selectedYearId' => $selectedYearId,
            'selectedTermId' => $selectedTermId,
            'canEditLockedTermDates' => TenantContext::isSuperAdminViewing(),
            'focusAcademicSession' => request()->routeIs('academic-session'),
        ]);
    }

    public function update(Request $request)
    {
        $request->merge([
            'email' => trim($request->email ?? '') ?: null,
            'website' => trim($request->website ?? '') ?: null,
            'motto' => trim($request->motto ?? '') ?: null,
        ]);

        $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string',
            'phone' => 'required|string|max:50',
            'email' => 'nullable|email|max:255',
            'website' => 'nullable|string|max:255',
            'motto' => 'nullable|string|max:255',
            'logo' => 'nullable|image|max:2048',
            'default_academic_year_id' => [
                'required',
                'exists:academic_years,id',
                function ($attribute, $value, $fail) {
                    if (! AcademicYear::where('id', $value)->where('status', 'Active')->exists()) {
                        $fail('The selected academic year must be active.');
                    }
                },
            ],
            'default_academic_term_id' => [
                'required',
                'exists:academic_terms,id',
                function ($attribute, $value, $fail) {
                    if (! AcademicTerm::where('id', $value)->where('status', 'Active')->exists()) {
                        $fail('The selected academic term must be active.');
                    }
                },
            ],
            'term_opening_date' => [
                'required',
                'date',
                function ($attribute, $value, $fail) use ($request) {
                    $this->rejectLockedTermDateChange($request, $attribute, $value, $fail);
                },
            ],
            'term_vacation_date' => [
                'required',
                'date',
                'after_or_equal:term_opening_date',
                function ($attribute, $value, $fail) use ($request) {
                    $this->rejectLockedTermDateChange($request, $attribute, $value, $fail);
                },
            ],
        ], [
            'term_opening_date.required' => 'The opening date is required for the selected term.',
            'term_vacation_date.required' => 'The vacation date is required for the selected term.',
            'term_vacation_date.after_or_equal' => 'The vacation date must be on or after the opening date.',
        ]);

        $school = SchoolSetting::current();
        $school->name = trim($request->name);
        $school->motto = $request->motto;
        $school->address = trim($request->address);
        $school->phone = trim($request->phone);
        $school->email = $request->email;
        $school->website = $request->website;
        $school->default_academic_year_id = $request->default_academic_year_id;
        $school->default_academic_term_id = $request->default_academic_term_id;
        $school->updated_by = Auth::id();

        if ($request->hasFile('logo')) {
            $school->logo_path = $this->uploadLogo($request->file('logo'), $school->logo_path);
        }

        $school->save();

        $schoolId = (int) ($school->school_id ?: TenantContext::schoolId());
        $yearId = (int) $request->default_academic_year_id;
        $termId = (int) $request->default_academic_term_id;
        $existingCalendar = $this->savedTermCalendar($schoolId, $yearId, $termId);
        $datesLocked = $this->termDatesAreLocked($existingCalendar);

        if (! $datesLocked) {
            AcademicTermCalendar::query()->updateOrCreate(
                [
                    'school_id' => $schoolId,
                    'academic_year_id' => $yearId,
                    'academic_term_id' => $termId,
                ],
                [
                    'opening_date' => $request->term_opening_date,
                    'vacation_date' => $request->term_vacation_date,
                    'updated_by' => Auth::id(),
                ]
            );
        }

        $tenantSchool = School::query()->find($schoolId);

        if ($tenantSchool && ! $tenantSchool->isSuspendedByAdmin()) {
            app(SchoolSubscriptionService::class)->syncAccessFromVacation($tenantSchool);
            $tenantSchool->refresh();

            if ($tenantSchool->isSubscriptionExpired() || $tenantSchool->isSuspendedForSubscription()) {
                return $this->logoutBecauseVacationIsDue($request, $tenantSchool);
            }
        }

        return redirect()
            ->route('school-settings')
            ->with('message_success', 'School information saved successfully.');
    }

    private function uploadLogo($file, $oldPath = null)
    {
        if (!empty($oldPath) && file_exists(public_path($oldPath))) {
            unlink(public_path($oldPath));
        }

        $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
        $folder = 'uploads/school';

        if (!file_exists(public_path($folder))) {
            mkdir(public_path($folder), 0755, true);
        }

        $file->move(public_path($folder), $filename);

        return $folder . '/' . $filename;
    }

    private function savedTermCalendar(int $schoolId, int $yearId, int $termId): ?AcademicTermCalendar
    {
        if (! $schoolId || ! $yearId || ! $termId) {
            return null;
        }

        return AcademicTermCalendar::query()
            ->where('school_id', $schoolId)
            ->where('academic_year_id', $yearId)
            ->where('academic_term_id', $termId)
            ->first();
    }

    private function termDatesAreLocked(?AcademicTermCalendar $calendar): bool
    {
        if (TenantContext::isSuperAdminViewing()) {
            return false;
        }

        return (bool) ($calendar?->opening_date && $calendar?->vacation_date);
    }

    private function rejectLockedTermDateChange(Request $request, string $attribute, mixed $value, callable $fail): void
    {
        $schoolId = (int) (SchoolSetting::current()->school_id ?: TenantContext::schoolId());
        $calendar = $this->savedTermCalendar(
            $schoolId,
            (int) $request->default_academic_year_id,
            (int) $request->default_academic_term_id,
        );

        if (! $this->termDatesAreLocked($calendar)) {
            return;
        }

        $saved = $attribute === 'term_opening_date'
            ? $calendar->opening_date?->format('Y-m-d')
            : $calendar->vacation_date?->format('Y-m-d');

        if ($saved && Carbon::parse($value)->toDateString() !== $saved) {
            $fail('Opening and vacation dates for this academic year and term are locked. Ask the platform administrator to change them.');
        }
    }

    private function logoutBecauseVacationIsDue(Request $request, School $school): RedirectResponse
    {
        $user = Auth::user();
        $isAdmin = $user && SchoolAdminCategory::userIsAdmin($user, $school);
        $code = $school->code;

        $logId = (int) $request->session()->get('userLogId');
        if ($logId > 0) {
            $log = UsrUserLog::query()->find($logId);
            if ($log) {
                $log->logout_date = Carbon::now();
                $log->update();
            }
        }

        $request->session()->forget('userLogId');
        Auth::logout();
        TenantContext::clear();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($isAdmin && $code) {
            return redirect()->route('admin-login')
                ->with('login_error_message', 'Your school subscription has ended. Renew to sign in.')
                ->with('subscription_renew_url', route('renew-subscription', ['school_code' => $code]));
        }

        return redirect()->route('admin-login')
            ->with('login_error_message', 'Subscription has ended. Ask your school administrator to renew.');
    }
}
