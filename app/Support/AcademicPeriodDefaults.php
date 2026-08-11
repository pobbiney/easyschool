<?php

namespace App\Support;

use App\Models\SchoolSetting;
use Illuminate\Http\Request;

class AcademicPeriodDefaults
{
    public static function yearId(?Request $request = null, string $key = 'academic_year_id'): ?int
    {
        if ($request && $request->has($key)) {
            $value = $request->input($key);

            return ($value !== '' && $value !== null) ? (int) $value : null;
        }

        return SchoolSetting::current()->defaultAcademicYearId();
    }

    public static function termId(?Request $request = null, string $key = 'academic_term_id'): ?int
    {
        if ($request && $request->has($key)) {
            $value = $request->input($key);

            return ($value !== '' && $value !== null) ? (int) $value : null;
        }

        return SchoolSetting::current()->defaultAcademicTermId();
    }

    public static function forFrontend(): array
    {
        $school = SchoolSetting::current()->load(['defaultAcademicYear', 'defaultAcademicTerm']);

        $yearId = $school->defaultAcademicYearId();
        $termId = $school->defaultAcademicTermId();

        return [
            'year_id' => $yearId,
            'term_id' => $termId,
            'year_name' => $yearId ? $school->defaultAcademicYear?->name : null,
            'term_name' => $termId ? $school->defaultAcademicTerm?->name : null,
        ];
    }
}
