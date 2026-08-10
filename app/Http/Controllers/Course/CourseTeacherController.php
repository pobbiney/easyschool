<?php

namespace App\Http\Controllers\Course;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseTeachingAssignment;
use App\Models\SchoolClass;
use App\Models\Staff;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CourseTeacherController extends Controller
{
    private const TEACHER_CATEGORY_ID = 2;

    public function index()
    {
        $topLevelCourses = Course::topLevel()
            ->with([
                'subCourses.teachingAssignments.teacher',
                'subCourses.teachingAssignments.schoolClass',
                'teachingAssignments.teacher',
                'teachingAssignments.schoolClass',
            ])
            ->orderBy('name')
            ->get();

        $schoolClasses = SchoolClass::where('status', 'Active')->orderBy('name')->get();
        $teachers = $this->teacherStaffQuery()->get();

        $assignmentCount = CourseTeachingAssignment::count();
        $totalSlots = $this->countAssignableSlots($topLevelCourses, $schoolClasses->count());

        return view('course-setup.course-teacher-assignment', [
            'topLevelCourses' => $topLevelCourses,
            'schoolClasses' => $schoolClasses,
            'teachers' => $teachers,
            'stats' => [
                'total_courses' => $topLevelCourses->count(),
                'assignments' => $assignmentCount,
                'unassigned' => max($totalSlots - $assignmentCount, 0),
                'teachers' => $teachers->count(),
            ],
        ]);
    }

    public function show($id)
    {
        $course = Course::with(['parent', 'teachingAssignments.teacher', 'teachingAssignments.schoolClass'])
            ->findOrFail($id);

        return response()->json([
            'id' => $course->id,
            'name' => $course->name,
            'parent_name' => $course->parent?->name,
            'is_sub_course' => $course->isSubCourse(),
            'assignments' => $course->teachingAssignments->map(function ($assignment) {
                return [
                    'id' => $assignment->id,
                    'school_class_id' => $assignment->school_class_id,
                    'class_name' => $assignment->schoolClass?->name,
                    'staff_id' => $assignment->staff_id,
                    'teacher_name' => $assignment->teacher?->full_name,
                    'teacher_position' => $assignment->teacher?->position,
                ];
            })->values(),
        ]);
    }

    public function assign(Request $request)
    {
        $request->validate([
            'course_id' => 'required|exists:courses,id',
            'school_class_id' => 'required|exists:school_classes,id',
            'staff_id' => 'required|exists:staff,id',
        ]);

        $course = Course::findOrFail($request->course_id);
        $schoolClass = SchoolClass::where('id', $request->school_class_id)->where('status', 'Active')->firstOrFail();
        $teacher = $this->teacherStaffQuery()->where('staff.id', $request->staff_id)->firstOrFail();

        $assignment = CourseTeachingAssignment::firstOrNew([
            'course_id' => $course->id,
            'school_class_id' => $schoolClass->id,
        ]);

        if (! $assignment->exists) {
            $assignment->created_by = Auth::id();
        }

        $assignment->staff_id = $teacher->id;
        $assignment->updated_by = Auth::id();
        $assignment->save();

        return back()->with('message_success', 'Course teacher assigned successfully.');
    }

    public function unassign(Request $request)
    {
        $request->validate([
            'assignment_id' => 'required|exists:course_teaching_assignments,id',
        ]);

        CourseTeachingAssignment::where('id', $request->assignment_id)->delete();

        return back()->with('message_success', 'Course teacher assignment removed successfully.');
    }

    private function teacherStaffQuery()
    {
        return Staff::query()
            ->where('staff.status', 'Active')
            ->whereHas('user', function ($query) {
                $query->where('user_cat', self::TEACHER_CATEGORY_ID)
                    ->where('status', 'Active');
            })
            ->orderBy('staff.surname')
            ->orderBy('staff.firstname');
    }

    private function countAssignableSlots($topLevelCourses, int $classCount): int
    {
        if ($classCount === 0) {
            return 0;
        }

        $courseCount = $topLevelCourses->sum(function ($course) {
            return 1 + $course->subCourses->count();
        });

        return $courseCount * $classCount;
    }
}
