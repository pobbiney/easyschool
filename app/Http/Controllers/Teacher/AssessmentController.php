<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\AcademicAssessment;
use App\Models\AssessmentScore;
use App\Models\Course;
use App\Models\GradingScheme;
use App\Models\SchoolClass;
use App\Services\GradingService;
use App\Services\TeacherAccessService;
use App\Support\AcademicPeriodDefaults;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AssessmentController extends Controller implements HasMiddleware
{
    public function __construct(
        private TeacherAccessService $teacherAccess,
        private GradingService $grading
    ) {}

    public static function middleware(): array
    {
        return ['auth', 'teacher'];
    }

    public function hub(Request $request)
    {
        $staffId = $this->teacherAccess->staffId();
        $yearId = AcademicPeriodDefaults::yearId($request);
        $termId = AcademicPeriodDefaults::termId($request);

        $homeroomClasses = $this->teacherAccess->homeroomClasses($staffId);
        $subjectAssignments = $this->teacherAccess->subjectAssignments($staffId, $yearId, $termId);

        $assessments = AcademicAssessment::query()
            ->with(['schoolClass', 'course', 'scores'])
            ->where('staff_id', $staffId)
            ->when($yearId, fn ($q) => $q->where('academic_year_id', $yearId))
            ->when($termId, fn ($q) => $q->where('academic_term_id', $termId))
            ->orderByDesc('created_at')
            ->get();

        $publishedCount = $assessments->where('status', 'published')->count();
        $draftCount = $assessments->where('status', 'draft')->count();
        $scoredEntries = $assessments->sum(fn ($a) => $a->scores->whereNotNull('score')->count());

        return view('teacher.assessments-hub', [
            'homeroomClasses' => $homeroomClasses,
            'subjectAssignments' => $subjectAssignments,
            'assessments' => $assessments,
            'period' => AcademicPeriodDefaults::forFrontend(),
            'stats' => [
                'total' => $assessments->count(),
                'published' => $publishedCount,
                'draft' => $draftCount,
                'homeroom_slots' => $homeroomClasses->count(),
                'subject_slots' => $subjectAssignments->count(),
                'scores_entered' => $scoredEntries,
            ],
        ]);
    }

    public function classIndex(Request $request, SchoolClass $class)
    {
        return $this->renderIndex($request, $class, null);
    }

    public function courseIndex(Request $request, Course $course, SchoolClass $class)
    {
        return $this->renderIndex($request, $class, $course);
    }

    private function renderIndex(Request $request, SchoolClass $class, ?Course $course = null)
    {
        $staffId = $this->teacherAccess->staffId();
        $yearId = AcademicPeriodDefaults::yearId($request);
        $termId = AcademicPeriodDefaults::termId($request);

        $this->teacherAccess->assertCanAccessClass($staffId, $class->id, $course?->id, $request);

        $assessments = AcademicAssessment::query()
            ->with(['schoolClass', 'course', 'scores'])
            ->where('staff_id', $staffId)
            ->where('school_class_id', $class->id)
            ->when($course, fn ($q) => $q->where('course_id', $course->id))
            ->when($yearId, fn ($q) => $q->where('academic_year_id', $yearId))
            ->when($termId, fn ($q) => $q->where('academic_term_id', $termId))
            ->orderByDesc('assessment_date')
            ->orderByDesc('created_at')
            ->get();

        $homeroomClasses = $this->teacherAccess->homeroomClasses($staffId);
        $subjectAssignments = $this->teacherAccess->subjectAssignments($staffId, $yearId, $termId);

        return view('teacher.assessments', [
            'assessments' => $assessments,
            'schoolClass' => $class,
            'course' => $course,
            'period' => AcademicPeriodDefaults::forFrontend(),
            'homeroomClasses' => $homeroomClasses,
            'subjectAssignments' => $subjectAssignments,
            'defaultClassId' => $class->id,
            'defaultCourseId' => $course?->id,
            'lockClass' => true,
        ]);
    }

    public function store(Request $request)
    {
        $staffId = $this->teacherAccess->staffId();

        $validated = $request->validate([
            'type' => 'required|in:'.implode(',', AcademicAssessment::TYPES),
            'title' => 'required|string|max:200',
            'description' => 'nullable|string|max:2000',
            'due_date' => 'nullable|date',
            'assessment_date' => 'nullable|date',
            'school_class_id' => 'required|exists:school_classes,id',
            'course_id' => 'required|exists:courses,id',
            'max_score' => 'required|numeric|min:1|max:9999',
            'status' => 'required|in:'.implode(',', AcademicAssessment::STATUSES),
        ]);

        $courseId = (int) $validated['course_id'];
        $this->teacherAccess->assertCanAccessClass($staffId, (int) $validated['school_class_id'], $courseId, $request);

        if (! $this->teacherAccess->ownsSubjectAssignment($staffId, $courseId, (int) $validated['school_class_id'], AcademicPeriodDefaults::yearId($request), AcademicPeriodDefaults::termId($request))
            && ! $this->teacherAccess->ownsHomeroomClass($staffId, (int) $validated['school_class_id'])) {
            return back()->with('message_error', 'You are not assigned to teach this course in this class.');
        }

        $yearId = AcademicPeriodDefaults::yearId($request);
        $termId = AcademicPeriodDefaults::termId($request);

        if (! $yearId || ! $termId) {
            return back()->with('message_error', 'Set a default academic year and term in school settings.');
        }

        AcademicAssessment::create([
            ...$validated,
            'course_id' => $courseId,
            'academic_year_id' => $yearId,
            'academic_term_id' => $termId,
            'staff_id' => $staffId,
            'created_by' => Auth::id(),
        ]);

        return back()->with('message_success', 'Assessment created successfully.');
    }

    public function scores(AcademicAssessment $assessment)
    {
        $staffId = $this->teacherAccess->staffId();
        $this->teacherAccess->assertCanAccessClass(
            $staffId,
            $assessment->school_class_id,
            $assessment->course_id,
            request()
        );

        $students = $this->teacherAccess->studentsForClass($assessment->school_class_id);
        $existingScores = $assessment->scores()->get()->keyBy('student_id');

        return view('teacher.assessment-scores', [
            'assessment' => $assessment->load(['schoolClass', 'course']),
            'students' => $students,
            'existingScores' => $existingScores,
            'gradingSchemes' => GradingScheme::orderByDesc('min_percentage')->get(),
            'stats' => [
                'total' => $students->count(),
                'scored' => $existingScores->filter(fn ($s) => $s->score !== null)->count(),
                'pending' => $students->count() - $existingScores->filter(fn ($s) => $s->score !== null)->count(),
            ],
        ]);
    }

    public function saveScores(Request $request, AcademicAssessment $assessment)
    {
        $staffId = $this->teacherAccess->staffId();
        $this->teacherAccess->assertCanAccessClass(
            $staffId,
            $assessment->school_class_id,
            $assessment->course_id,
            $request
        );

        $request->validate([
            'scores' => 'required|array',
            'scores.*.student_id' => 'required|exists:students,id',
            'scores.*.score' => 'nullable|numeric|min:0|max:'.$assessment->max_score,
            'scores.*.remarks' => 'nullable|string|max:500',
        ]);

        DB::transaction(function () use ($request, $assessment) {
            foreach ($request->scores as $row) {
                $scoreValue = ($row['score'] === '' || $row['score'] === null) ? null : (float) $row['score'];
                $graded = $this->grading->gradeScore($scoreValue, (float) $assessment->max_score);

                AssessmentScore::updateOrCreate(
                    [
                        'academic_assessment_id' => $assessment->id,
                        'student_id' => $row['student_id'],
                    ],
                    [
                        'score' => $scoreValue,
                        'letter_grade' => $graded['letter_grade'],
                        'remarks' => trim($row['remarks'] ?? '') ?: null,
                        'graded_by' => Auth::id(),
                        'graded_at' => $scoreValue !== null ? now() : null,
                    ]
                );
            }
        });

        return back()->with('message_success', 'Scores saved successfully.');
    }
}
