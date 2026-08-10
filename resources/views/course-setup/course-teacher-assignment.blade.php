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

    .parent-hint {
        display: block;
        margin-top: 4px;
        font-size: 12px;
        color: #6366f1;
        font-weight: 500;
    }

    .type-badge,
    .class-badge,
    .teacher-count-pill,
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

    .teacher-count-pill {
        border: none;
        min-width: 36px;
        justify-content: center;
        cursor: pointer;
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }

    .teacher-count-pill.is-active {
        background: rgba(37, 161, 148, 0.12);
        color: var(--primary-600, #25A194);
    }

    .teacher-count-pill.is-active:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(37, 161, 148, 0.15);
    }

    .teacher-count-pill.is-empty {
        background: var(--neutral-100, #f3f4f6);
        color: var(--neutral-500, #6b7280);
        cursor: default;
    }

    .teacher-empty-pill {
        gap: 6px;
        background: var(--neutral-100, #f3f4f6);
        color: var(--neutral-500, #6b7280);
    }

    .assigned-teachers-panel {
        margin-top: 8px;
        padding-top: 16px;
        border-top: 1px solid var(--neutral-200, #e5e7eb);
    }

    .assigned-teachers-list {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .assigned-teacher-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 12px 14px;
        border-radius: 12px;
        background: var(--neutral-50, #f9fafb);
        border: 1px solid var(--neutral-200, #e5e7eb);
    }

    .assigned-teacher-item .teacher-meta {
        display: flex;
        flex-direction: column;
        gap: 4px;
        min-width: 0;
    }

    .assigned-teacher-item .class-badge {
        align-self: flex-start;
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
            <p class="text-sm text-secondary-light mb-0">Assign teachers to courses across one or more classes. Multiple teachers can be assigned to the same course and class.</p>
        </div>
        <div class="card-body p-0 dataTable-wrapper">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-16 px-20 py-12 border-bottom border-neutral-200">
                <form class="navbar-search dt-search m-0">
                    <input type="text" class="dt-input bg-transparent radius-4" aria-controls="dataTable" name="search" placeholder="Search courses or teachers...">
                    <iconify-icon icon="ion:search-outline" class="icon"></iconify-icon>
                </form>
            </div>
            <div class="assignment-list-scroll p-0">
                <table class="table bordered-table mb-0 data-table" id="dataTable" data-page-length="10">
                    <thead>
                        <tr>
                            <th>Course / Sub-Course</th>
                            <th>Type</th>
                            <th>Teachers</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($assignableCourses as $course)
                            @include('course-setup.partials._course-teacher-rows', [
                                'course' => $course,
                                'isSubCourse' => $course->isSubCourse(),
                            ])
                        @empty
                            <tr>
                                <td></td>
                                <td class="text-center py-24 text-secondary-light">No courses available. Add courses first.</td>
                                <td></td>
                                <td></td>
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
        const assignModalEl = document.getElementById('assignCourseTeacherModal');
        const viewModalEl = document.getElementById('viewCourseTeachersModal');
        const assignModal = assignModalEl ? new bootstrap.Modal(assignModalEl) : null;
        const viewModal = viewModalEl ? new bootstrap.Modal(viewModalEl) : null;
        let currentAssignments = [];
        let activeCourseId = null;

        function assignmentsForClass(classId) {
            if (!classId) {
                return [];
            }

            return currentAssignments.filter(function (item) {
                return String(item.school_class_id) === String(classId);
            });
        }

        function teacherAvatarHtml(teacherName, picture) {
            if (picture) {
                return '<img src="' + picture + '" alt="' + teacherName + '">';
            }

            const parts = (teacherName || '').trim().split(/\s+/);
            const initials = ((parts[0] || '')[0] || '') + ((parts[1] || '')[0] || '');

            return initials.toUpperCase();
        }

        function renderAssignedTeachersList(containerSelector, assignments, emptyMessage, showClass) {
            const $container = $(containerSelector);

            if (!assignments.length) {
                $container.html('<p class="text-sm text-secondary-light mb-0">' + emptyMessage + '</p>');
                return;
            }

            const html = assignments.map(function (assignment) {
                const avatar = teacherAvatarHtml(assignment.teacher_name, assignment.teacher_picture);
                const classLabel = showClass && assignment.class_name
                    ? '<span class="class-badge">' + assignment.class_name + '</span>'
                    : '';

                return (
                    '<div class="assigned-teacher-item">' +
                        '<div class="teacher-name-cell">' +
                            '<span class="teacher-avatar">' + avatar + '</span>' +
                            '<div class="teacher-meta">' +
                                '<span class="fw-semibold">' + (assignment.teacher_name || 'Unknown teacher') + '</span>' +
                                classLabel +
                            '</div>' +
                        '</div>' +
                        '<button type="button" class="btn btn-sm btn-outline-danger-600 remove-assigned-teacher-btn" data-assignment-id="' + assignment.id + '">' +
                            'Remove' +
                        '</button>' +
                    '</div>'
                );
            }).join('');

            $container.html(html);
        }

        function refreshAssignedTeachersList() {
            const classId = $('#assign_school_class_id').val();
            const className = $('#assign_school_class_id option:selected').text();
            const assignments = classId
                ? assignmentsForClass(classId)
                : currentAssignments;

            $('#assignedTeachersClassLabel').text(
                classId ? className : (currentAssignments.length ? 'All classes' : 'Select a class')
            );
            renderAssignedTeachersList(
                '#assignedTeachersList',
                assignments,
                classId
                    ? 'No teachers assigned to this class yet.'
                    : (currentAssignments.length ? 'No teachers assigned to this course yet.' : 'Select a class to filter, or add a teacher above.'),
                true
            );
            $('#assign_staff_id').val('');
        }

        function refreshViewTeachersList() {
            if (!viewModalEl || !viewModalEl.classList.contains('show')) {
                return;
            }

            renderAssignedTeachersList(
                '#viewTeachersList',
                currentAssignments,
                'No teachers assigned to this course yet.',
                true
            );
        }

        function getCsrfToken() {
            return $('#assignCourseTeacherForm input[name="_token"]').val()
                || $('#unassignCourseTeacherForm input[name="_token"]').val();
        }

        function showToast(type, message) {
            if (typeof window.showAppToast === 'function') {
                window.showAppToast(type, message);
            }
        }

        function removeAssignment(assignmentId) {
            currentAssignments = currentAssignments.filter(function (item) {
                return String(item.id) !== String(assignmentId);
            });
            refreshAssignedTeachersList();
            refreshViewTeachersList();
            syncTableTeacherCounts(activeCourseId);
        }

        function buildTeacherCountPill(count, meta) {
            if (count > 0) {
                return (
                    '<button type="button" class="teacher-count-pill view-course-teachers-btn is-active"' +
                        ' data-course-id="' + meta.courseId + '"' +
                        ' data-course-name="' + meta.courseName + '"' +
                        ' data-url="' + meta.courseUrl + '">' +
                        count +
                    '</button>'
                );
            }

            return '<span class="teacher-count-pill is-empty">0</span>';
        }

        function updateCourseRowCount($row, count) {
            const meta = {
                courseId: $row.attr('data-course-id'),
                courseName: $row.attr('data-course-name'),
                courseUrl: $row.attr('data-course-url'),
            };

            $row.find('.course-teachers-cell').html(buildTeacherCountPill(count, meta));
        }

        function syncTableTeacherCounts(courseId) {
            if (!courseId) {
                return;
            }

            const $row = $('tr.course-group-row[data-course-id="' + courseId + '"]').first();
            if (!$row.length) {
                return;
            }

            updateCourseRowCount($row, currentAssignments.length);
        }

        $('body').on('click', '.assign-course-teacher-btn', function () {
            const button = $(this);

            $.getJSON(button.data('url'), function (data) {
                currentAssignments = data.assignments || [];
                activeCourseId = data.id;

                $('#assign_course_id').val(data.id);
                $('#assign_course_name').text(data.name);
                $('#assign_course_name_inline').text(data.parent_name ? data.parent_name + ' / ' + data.name : data.name);
                $('#assign_course_type_label').text(data.is_sub_course ? 'Sub-Course' : 'Course');
                $('#assign_school_class_id').val('');
                refreshAssignedTeachersList();

                if (assignModal) {
                    assignModal.show();
                }
            }).fail(function () {
                showToast('error', 'Unable to load course teacher assignments.');
            });
        });

        $('body').on('click', '.view-course-teachers-btn.is-active', function () {
            const button = $(this);

            $.getJSON(button.data('url'), function (data) {
                currentAssignments = data.assignments || [];
                activeCourseId = data.id;

                $('#view_teachers_course_name').text(data.parent_name ? data.parent_name + ' / ' + data.name : data.name);
                renderAssignedTeachersList(
                    '#viewTeachersList',
                    currentAssignments,
                    'No teachers assigned to this course yet.',
                    true
                );

                if (viewModal) {
                    viewModal.show();
                }
            }).fail(function () {
                showToast('error', 'Unable to load assigned teachers.');
            });
        });

        $(document).on('change', '#assign_school_class_id', refreshAssignedTeachersList);

        if (assignModalEl) {
            assignModalEl.addEventListener('shown.bs.modal', refreshAssignedTeachersList);
        }

        $('#assignCourseTeacherForm').on('submit', function (event) {
            event.preventDefault();

            const $form = $(this);
            const $submitBtn = $form.find('[type="submit"]');

            $submitBtn.prop('disabled', true);

            $.ajax({
                url: $form.attr('action'),
                method: 'POST',
                data: $form.serialize(),
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                success: function (response) {
                    if (response.assignment) {
                        currentAssignments.push(response.assignment);
                    }

                    activeCourseId = $('#assign_course_id').val();
                    refreshAssignedTeachersList();
                    refreshViewTeachersList();
                    syncTableTeacherCounts(activeCourseId);
                    showToast('success', response.message || 'Course teacher assigned successfully.');
                },
                error: function (xhr) {
                    const message = xhr.responseJSON?.message
                        || xhr.responseJSON?.errors?.staff_id?.[0]
                        || xhr.responseJSON?.errors?.school_class_id?.[0]
                        || 'Unable to assign teacher.';

                    showToast('error', message);
                },
                complete: function () {
                    $submitBtn.prop('disabled', false);
                },
            });
        });

        $('body').on('click', '.remove-assigned-teacher-btn', function () {
            if (!confirm('Remove this teacher assignment?')) {
                return;
            }

            const assignmentId = $(this).data('assignment-id');
            const $button = $(this);

            $button.prop('disabled', true);

            $.ajax({
                url: $('#unassignCourseTeacherForm').attr('action'),
                method: 'POST',
                data: {
                    _token: getCsrfToken(),
                    assignment_id: assignmentId,
                },
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                success: function (response) {
                    removeAssignment(response.assignment_id || assignmentId);
                    showToast('success', response.message || 'Course teacher assignment removed successfully.');
                },
                error: function (xhr) {
                    const message = xhr.responseJSON?.message || 'Unable to remove teacher assignment.';
                    showToast('error', message);
                    $button.prop('disabled', false);
                },
            });
        });
    })();
</script>
@endsection
