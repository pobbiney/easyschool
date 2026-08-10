@php $pageName = "course"; $subpageName = "course-teacher-assignment"; @endphp

@extends('layouts.app')

@section('css')
<style>
    .assignment-stat-card {
        border: 1px solid var(--neutral-200, #e5e7eb);
        border-radius: 12px;
        padding: 18px 20px;
        background: var(--white, #fff);
        height: 100%;
    }

    .assignment-stat-card .stat-icon {
        width: 42px;
        height: 42px;
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
    }

    .assignment-list-scroll {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    .assignment-list-wrapper table.dataTable {
        min-width: 980px;
    }

    .course-name-cell,
    .teacher-name-cell {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .course-avatar,
    .teacher-avatar {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        flex-shrink: 0;
        overflow: hidden;
        background: rgba(37, 161, 148, 0.1);
        color: var(--primary-600, #25A194);
    }

    .teacher-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .course-group-row td {
        background: var(--neutral-50, #f9fafb);
    }

    .subcourse-row .course-name-cell {
        padding-left: 24px;
    }

    .type-badge,
    .position-badge,
    .class-badge,
    .teacher-empty-pill {
        display: inline-flex;
        align-items: center;
        padding: 4px 10px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 600;
    }

    .type-badge {
        background: rgba(37, 161, 148, 0.08);
        color: var(--primary-600, #25A194);
    }

    .class-badge {
        background: var(--neutral-100, #f3f4f6);
        color: var(--neutral-600, #4b5563);
    }

    .position-badge {
        background: rgba(37, 161, 148, 0.08);
        color: var(--primary-600, #25A194);
        font-weight: 500;
    }

    .teacher-empty-pill {
        gap: 6px;
        background: var(--neutral-100, #f3f4f6);
        color: var(--neutral-500, #6b7280);
    }

    .assign-course-preview {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 14px 16px;
        border-radius: 12px;
        background: var(--neutral-50, #f9fafb);
        border: 1px solid var(--neutral-200, #e5e7eb);
    }

    .assign-course-preview-icon {
        width: 44px;
        height: 44px;
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: rgba(37, 161, 148, 0.12);
        color: var(--primary-600, #25A194);
        font-size: 20px;
    }

    .current-teacher-box {
        padding: 14px 16px;
        border-radius: 12px;
        background: rgba(37, 161, 148, 0.05);
        border: 1px dashed rgba(37, 161, 148, 0.25);
    }
</style>
@endsection

@section('content')

<div class="dashboard-main-body">

    <div class="breadcrumb d-flex flex-wrap align-items-center justify-content-between gap-3 mb-24">
        <div>
            <h1 class="fw-semibold mb-4 h6 text-primary-light">COURSE SETUP</h1>
            <div>
                <a href="{{ route('dashboard') }}" class="text-secondary-light hover-text-primary hover-underline">Dashboard</a>
                <a href="{{ route('add-course') }}" class="text-secondary-light hover-text-primary hover-underline"> / Courses</a>
                <span class="text-secondary-light"> / Course Teachers</span>
            </div>
        </div>
        <a href="{{ route('add-course') }}" class="btn btn-outline-primary-600 d-flex align-items-center gap-6">
            <i class="ri-book-open-line"></i>
            Manage Courses
        </a>
    </div>

    <div class="row gy-4 mb-24">
        <div class="col-sm-6 col-xl-3">
            <div class="assignment-stat-card">
                <div class="d-flex align-items-center justify-content-between gap-3">
                    <div>
                        <p class="text-secondary-light text-sm mb-4">Total Courses</p>
                        <h4 class="fw-semibold mb-0">{{ $stats['total_courses'] }}</h4>
                    </div>
                    <span class="stat-icon bg-primary-50 text-primary-600"><i class="ri-book-open-line"></i></span>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="assignment-stat-card">
                <div class="d-flex align-items-center justify-content-between gap-3">
                    <div>
                        <p class="text-secondary-light text-sm mb-4">Assignments</p>
                        <h4 class="fw-semibold mb-0 text-success-600">{{ $stats['assignments'] }}</h4>
                    </div>
                    <span class="stat-icon bg-success-100 text-success-600"><i class="ri-user-follow-line"></i></span>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="assignment-stat-card">
                <div class="d-flex align-items-center justify-content-between gap-3">
                    <div>
                        <p class="text-secondary-light text-sm mb-4">Open Slots</p>
                        <h4 class="fw-semibold mb-0 text-warning-600">{{ $stats['unassigned'] }}</h4>
                    </div>
                    <span class="stat-icon bg-warning-100 text-warning-600"><i class="ri-user-unfollow-line"></i></span>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="assignment-stat-card">
                <div class="d-flex align-items-center justify-content-between gap-3">
                    <div>
                        <p class="text-secondary-light text-sm mb-4">Teacher Category</p>
                        <h4 class="fw-semibold mb-0">{{ $stats['teachers'] }}</h4>
                    </div>
                    <span class="stat-icon bg-info-100 text-info-600"><i class="ri-team-line"></i></span>
                </div>
            </div>
        </div>
    </div>

    <div class="card h-100 assignment-list-wrapper">
        <div class="card-header border-bottom bg-base py-16 px-24">
            <h6 class="text-lg fw-semibold mb-4">Course Teacher Assignments</h6>
            <p class="text-sm text-secondary-light mb-0">Assign teachers to courses or sub-courses for specific classes. One teacher per course and class.</p>
        </div>
        <div class="card-body p-0 dataTable-wrapper">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-16 px-20 py-12 border-bottom border-neutral-200">
                <form class="navbar-search dt-search m-0">
                    <input type="text" class="dt-input bg-transparent radius-4" aria-controls="dataTable" name="search" placeholder="Search courses, classes, or teachers...">
                    <iconify-icon icon="ion:search-outline" class="icon"></iconify-icon>
                </form>
            </div>
            <div class="assignment-list-scroll p-0">
                <table class="table bordered-table mb-0 data-table" id="dataTable" data-page-length="10">
                    <thead>
                        <tr>
                            <th>Course / Sub-Course</th>
                            <th>Type</th>
                            <th>Class</th>
                            <th>Assigned Teacher</th>
                            <th>Position</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($topLevelCourses as $course)
                            @include('course-setup.partials._course-teacher-rows', ['course' => $course, 'isSubCourse' => false])
                            @foreach($course->subCourses as $subCourse)
                                @include('course-setup.partials._course-teacher-rows', ['course' => $subCourse, 'isSubCourse' => true])
                            @endforeach
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-24 text-secondary-light">No courses available. Add courses first.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@include('course-setup.modals.assign-course-teacher-modal')

@endsection

@section('scripts')
<script>
    (function () {
        const modalEl = document.getElementById('assignCourseTeacherModal');
        const modal = modalEl ? new bootstrap.Modal(modalEl) : null;
        let currentAssignments = [];

        function findAssignmentForClass(classId) {
            return currentAssignments.find(function (item) {
                return String(item.school_class_id) === String(classId);
            });
        }

        function refreshCurrentAssignmentPreview() {
            const classId = $('#assign_school_class_id').val();
            const assignment = findAssignmentForClass(classId);

            if (assignment && assignment.teacher_name) {
                $('#currentAssignmentBox').show();
                $('#currentAssignmentTeacher').text(assignment.teacher_name);
                $('#currentAssignmentMeta').text((assignment.teacher_position || '') + ' • ' + (assignment.class_name || ''));
                $('#assign_staff_id').val(assignment.staff_id || '');
                $('#unassign_assignment_id').val(assignment.id || '');
                $('#unassignCourseTeacherBtn').toggle(!!assignment.id);
            } else {
                $('#currentAssignmentBox').hide();
                $('#assign_staff_id').val('');
                $('#unassign_assignment_id').val('');
                $('#unassignCourseTeacherBtn').hide();
            }
        }

        $('body').on('click', '.assign-course-teacher-btn', function () {
            const button = $(this);
            const presetClassId = button.data('class-id') || '';

            $.get(button.data('url'), function (data) {
                currentAssignments = data.assignments || [];

                $('#assign_course_id').val(data.id);
                $('#assign_course_name').text(data.name);
                $('#assign_course_name_inline').text(data.parent_name ? data.parent_name + ' / ' + data.name : data.name);
                $('#assign_course_type_label').text(data.is_sub_course ? 'Sub-Course' : 'Course');
                $('#assign_school_class_id').val(presetClassId);
                refreshCurrentAssignmentPreview();

                if (modal) {
                    modal.show();
                }
            });
        });

        $('#assign_school_class_id').on('change', refreshCurrentAssignmentPreview);

        $('#unassignCourseTeacherBtn').on('click', function () {
            if ($('#unassign_assignment_id').val() && confirm('Remove this assignment?')) {
                $('#unassignCourseTeacherForm').trigger('submit');
            }
        });
    })();
</script>
@endsection
