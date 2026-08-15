<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\AcademicAssessment;
use App\Models\AssessmentScore;
use App\Models\AssessmentType;
use App\Models\AcademicTerm;
use App\Models\AcademicYear;
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
        return $this->renderHub($request, 'pending');
    }

    public function records(Request $request)
    {
        return $this->renderHub($request, 'records');
    }

    public function classIndex(Request $request, SchoolClass $class)
    {
        return $this->renderIndex($request, $class, null, 'pending');
    }

    public function courseIndex(Request $request, Course $course, SchoolClass $class)
    {
        return $this->renderIndex($request, $class, $course, 'pending');
    }

    public function classRecords(Request $request, SchoolClass $class)
    {
        return $this->renderIndex($request, $class, null, 'records');
    }

    public function courseRecords(Request $request, Course $course, SchoolClass $class)
    {
        return $this->renderIndex($request, $class, $course, 'records');
    }

    private function renderHub(Request $request, string $scope)
    {
        $staffId = $this->teacherAccess->staffId();
        $yearId = AcademicPeriodDefaults::yearId($request);
        $termId = AcademicPeriodDefaults::termId($request);

        $homeroomClasses = $this->teacherAccess->homeroomClasses($staffId);
        $subjectAssignments = $this->teacherAccess->subjectAssignments($staffId, $yearId, $termId);

        $assessments = $this->teacherAssessmentsQuery($request, $scope)->get();

        $publishedCount = $assessments->where('status', 'published')->count();
        $draftCount = $assessments->where('status', 'draft')->count();
        $scoredEntries = $assessments->sum(fn ($a) => $a->scores->whereNotNull('score')->count());

        $pendingCount = $scope === 'pending'
            ? $assessments->count()
            : $this->teacherAssessmentsQuery($request, 'pending')->count();

        $recordsCount = $scope === 'records'
            ? $assessments->count()
            : $this->teacherAssessmentsQuery($request, 'records')->count();

        $view = $scope === 'records' ? 'teacher.assessment-records' : 'teacher.assessments-hub';

        return view($view, [
            'homeroomClasses' => $homeroomClasses,
            'subjectAssignments' => $subjectAssignments,
            'assessments' => $assessments,
            'assessmentTypes' => $this->activeAssessmentTypes(),
            ...$this->assessmentPageData($request),
            'stats' => [
                'total' => $assessments->count(),
                'published' => $publishedCount,
                'draft' => $draftCount,
                'homeroom_slots' => $homeroomClasses->count(),
                'subject_slots' => $subjectAssignments->count(),
                'scores_entered' => $scoredEntries,
                'pending' => $pendingCount,
                'records' => $recordsCount,
            ],
        ]);
    }

    private function renderIndex(Request $request, SchoolClass $class, ?Course $course = null, string $scope = 'pending')
    {
        $staffId = $this->teacherAccess->staffId();
        $yearId = AcademicPeriodDefaults::yearId($request);
        $termId = AcademicPeriodDefaults::termId($request);

        $this->teacherAccess->assertCanAccessClass($staffId, $class->id, $course?->id, $request);

        $assessments = $this->teacherAssessmentsQuery($request, $scope, $class->id, $course?->id)
            ->orderByDesc('assessment_date')
            ->get();

        $homeroomClasses = $this->teacherAccess->homeroomClasses($staffId);
        $subjectAssignments = $this->teacherAccess->subjectAssignments($staffId, $yearId, $termId);

        $pendingCount = $scope === 'pending'
            ? $assessments->count()
            : $this->teacherAssessmentsQuery($request, 'pending', $class->id, $course?->id)->count();

        $recordsCount = $scope === 'records'
            ? $assessments->count()
            : $this->teacherAssessmentsQuery($request, 'records', $class->id, $course?->id)->count();

        return view('teacher.assessments', [
            'assessments' => $assessments,
            'schoolClass' => $class,
            'course' => $course,
            ...$this->assessmentPageData($request),
            'homeroomClasses' => $homeroomClasses,
            'subjectAssignments' => $subjectAssignments,
            'assessmentTypes' => $this->activeAssessmentTypes(),
            'defaultClassId' => $class->id,
            'defaultCourseId' => $course?->id,
            'lockClass' => true,
            'scope' => $scope,
            'stats' => [
                'pending' => $pendingCount,
                'records' => $recordsCount,
            ],
        ]);
    }

    private function teacherAssessmentsQuery(Request $request, string $scope, ?int $classId = null, ?int $courseId = null)
    {
        $staffId = $this->teacherAccess->staffId();
        $yearId = AcademicPeriodDefaults::yearId($request);
        $termId = AcademicPeriodDefaults::termId($request);

        $query = AcademicAssessment::query()
            ->with(['schoolClass', 'course', 'scores', 'assessmentType', 'academicYear', 'academicTerm'])
            ->where('staff_id', $staffId)
            ->when($yearId, fn ($q) => $q->where('academic_year_id', $yearId))
            ->when($termId, fn ($q) => $q->where('academic_term_id', $termId))
            ->when($classId, fn ($q) => $q->where('school_class_id', $classId))
            ->when($courseId, fn ($q) => $q->where('course_id', $courseId));

        if ($scope === 'records') {
            $query->withRecordedScores();
        } else {
            $query->withoutRecordedScores();
        }

        return $query->orderByDesc('created_at');
    }

    public function store(Request $request)
    {
        $staffId = $this->teacherAccess->staffId();

        $validated = $request->validate([
            'type' => 'required|in:'.implode(',', AssessmentType::activeSlugs()),
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
            'assessment' => $assessment->load(['schoolClass', 'course', 'assessmentType', 'academicYear', 'academicTerm']),
            'students' => $students,
            'existingScores' => $existingScores,
            'gradingSchemes' => GradingScheme::orderByDesc('min_percentage')->get(),
            'period' => AcademicPeriodDefaults::forFrontend(request()),
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
        ], [
            'scores.*.score.min' => 'Score cannot be negative.',
            'scores.*.score.max' => 'Score cannot exceed the maximum of '.number_format((float) $assessment->max_score, 2).'.',
            'scores.*.score.numeric' => 'Please enter a valid score.',
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

    public function destroy(AcademicAssessment $assessment)
    {
        $staffId = $this->teacherAccess->staffId();

        if ((int) $assessment->staff_id !== (int) $staffId) {
            abort(403, 'You can only delete assessments you created.');
        }

        $this->teacherAccess->assertCanAccessClass(
            $staffId,
            $assessment->school_class_id,
            $assessment->course_id,
            request()
        );

        if ($assessment->hasRecordedScores()) {
            return back()->with('message_error', 'This assessment cannot be deleted because marks have already been entered.');
        }

        $assessment->delete();

        return back()->with('message_success', 'Assessment deleted successfully.');
    }

    private function activeAssessmentTypes()
    {
        return AssessmentType::query()
            ->active()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }

    private function assessmentPageData(Request $request): array
    {
        return [
            'period' => AcademicPeriodDefaults::forFrontend($request),
            'academicYears' => AcademicYear::query()->where('status', 'Active')->orderByDesc('name')->get(),
            'academicTerms' => AcademicTerm::query()->where('status', 'Active')->orderBy('sort_order')->get(),
        ];
    }
}
