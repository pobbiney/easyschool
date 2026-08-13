<?php

namespace App\Services;

use App\Models\AcademicAssessment;
use App\Models\AssessmentScore;
use App\Models\ClassAttendance;
use App\Models\Course;
use App\Models\Student;
use Illuminate\Support\Collection;

class GradebookService
{
    public function __construct(private GradingService $grading) {}

    public function classGradebook(int $classId, int $yearId, int $termId): array
    {
        $assessments = AcademicAssessment::query()
            ->with(['course', 'scores'])
            ->where('school_class_id', $classId)
            ->where('academic_year_id', $yearId)
            ->where('academic_term_id', $termId)
            ->where('status', 'published')
            ->orderBy('course_id')
            ->orderBy('type')
            ->orderBy('assessment_date')
            ->get();

        $students = Student::query()
            ->where('school_class_id', $classId)
            ->where('status', 'Active')
            ->orderBy('surname')
            ->orderBy('firstname')
            ->get();

        $byCourse = $assessments->groupBy(fn ($a) => $a->course_id ?? 0);

        $courseSummaries = $byCourse->map(function (Collection $courseAssessments, $courseId) use ($students) {
            $course = $courseId ? $courseAssessments->first()->course : null;

            $studentRows = $students->map(function (Student $student) use ($courseAssessments) {
                $percentages = $courseAssessments->map(function (AcademicAssessment $assessment) use ($student) {
                    $score = $assessment->scores->firstWhere('student_id', $student->id);

                    return $score && $score->score !== null
                        ? $this->grading->gradeScore((float) $score->score, (float) $assessment->max_score)['percentage']
                        : null;
                })->filter(fn ($p) => $p !== null);

                $average = $percentages->isNotEmpty()
                    ? round($percentages->avg(), 2)
                    : null;

                return [
                    'student' => $student,
                    'average_percentage' => $average,
                    'letter_grade' => $this->grading->letterGradeForPercentage($average),
                    'assessment_scores' => $courseAssessments->mapWithKeys(function (AcademicAssessment $assessment) use ($student) {
                        $score = $assessment->scores->firstWhere('student_id', $student->id);

                        return [$assessment->id => $score];
                    }),
                ];
            });

            return [
                'course' => $course,
                'course_name' => $course?->name ?? 'Homeroom Activities',
                'assessments' => $courseAssessments,
                'students' => $studentRows,
            ];
        })->values();

        $termAverages = $students->map(function (Student $student) use ($assessments) {
            $percentages = $assessments->map(function (AcademicAssessment $assessment) use ($student) {
                $score = $assessment->scores->firstWhere('student_id', $student->id);

                return $score && $score->score !== null
                    ? $this->grading->gradeScore((float) $score->score, (float) $assessment->max_score)['percentage']
                    : null;
            })->filter(fn ($p) => $p !== null);

            $average = $percentages->isNotEmpty() ? round($percentages->avg(), 2) : null;

            return [
                'student' => $student,
                'average_percentage' => $average,
                'letter_grade' => $this->grading->letterGradeForPercentage($average),
            ];
        });

        return [
            'assessments' => $assessments,
            'course_summaries' => $courseSummaries,
            'term_averages' => $termAverages,
        ];
    }

    public function studentReportCard(Student $student, int $yearId, int $termId): array
    {
        $gradebook = $this->classGradebook((int) $student->school_class_id, $yearId, $termId);

        $attendance = ClassAttendance::query()
            ->where('student_id', $student->id)
            ->where('academic_year_id', $yearId)
            ->where('academic_term_id', $termId)
            ->get();

        $attendanceSummary = [
            'present' => $attendance->where('status', 'present')->count(),
            'absent' => $attendance->where('status', 'absent')->count(),
            'late' => $attendance->where('status', 'late')->count(),
            'excused' => $attendance->where('status', 'excused')->count(),
            'total_days' => $attendance->count(),
        ];

        $termAverage = $gradebook['term_averages']->firstWhere('student.id', $student->id);

        $subjectGrades = $gradebook['course_summaries']->map(function ($summary) use ($student) {
            $row = $summary['students']->firstWhere('student.id', $student->id);

            return [
                'course_name' => $summary['course_name'],
                'average_percentage' => $row['average_percentage'] ?? null,
                'letter_grade' => $row['letter_grade'] ?? null,
                'assessments' => $summary['assessments']->map(function (AcademicAssessment $assessment) use ($row) {
                    $score = $row['assessment_scores'][$assessment->id] ?? null;

                    return [
                        'title' => $assessment->title,
                        'type' => $assessment->typeLabel(),
                        'score' => $score?->score,
                        'max_score' => $assessment->max_score,
                        'letter_grade' => $score?->letter_grade,
                    ];
                }),
            ];
        });

        return [
            'student' => $student,
            'term_average' => $termAverage,
            'subject_grades' => $subjectGrades,
            'attendance' => $attendanceSummary,
        ];
    }
}
