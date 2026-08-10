@php
    $courseLabel = $isSubCourse ? 'Sub-Course' : 'Course';
    $initials = strtoupper(substr($course->name, 0, 2));
    $assignmentsByClass = $course->teachingAssignments->groupBy('school_class_id');
    $courseUrl = route('get-course-teacher-assignment', $course->id);
@endphp

@if($assignmentsByClass->isEmpty())
    <tr class="course-group-row"
        data-course-id="{{ $course->id }}"
        data-class-id=""
        data-course-url="{{ $courseUrl }}"
        data-course-name="{{ $course->name }}"
        data-course-type="{{ $courseLabel }}">
        <td>
            <div class="course-name-cell">
                <span class="course-avatar">{{ $initials }}</span>
                <div>
                    <span class="fw-semibold text-primary-600">{{ $course->name }}</span>
                    @if($isSubCourse && $course->parent)
                        <span class="parent-hint">Under: {{ $course->parent->name }}</span>
                    @endif
                </div>
            </div>
        </td>
        <td><span class="type-badge">{{ $courseLabel }}</span></td>
        <td class="course-class-cell"><span class="text-secondary-light">—</span></td>
        <td class="course-teachers-cell"><span class="teacher-count-pill is-empty">0</span></td>
        <td>
            <button type="button" class="btn btn-sm btn-outline-primary-600 assign-course-teacher-btn"
                data-course-id="{{ $course->id }}"
                data-url="{{ $courseUrl }}">
                <i class="ri-user-shared-line"></i> Assign Teacher
            </button>
        </td>
    </tr>
@else
    @foreach($assignmentsByClass as $classId => $classAssignments)
        @php
            $className = $classAssignments->first()->schoolClass?->name ?? '—';
            $teacherCount = $classAssignments->count();
        @endphp
        <tr class="course-group-row"
            data-course-id="{{ $course->id }}"
            data-class-id="{{ $classId }}"
            data-course-url="{{ $courseUrl }}"
            data-course-name="{{ $course->name }}"
            data-course-type="{{ $courseLabel }}"
            data-class-name="{{ $className }}">
            <td>
                <div class="course-name-cell">
                    <span class="course-avatar">{{ $initials }}</span>
                    <div>
                        <span class="fw-semibold text-primary-600">{{ $course->name }}</span>
                        @if($isSubCourse && $course->parent)
                            <span class="parent-hint">Under: {{ $course->parent->name }}</span>
                        @endif
                    </div>
                </div>
            </td>
            <td><span class="type-badge">{{ $courseLabel }}</span></td>
            <td class="course-class-cell"><span class="class-badge">{{ $className }}</span></td>
            <td class="course-teachers-cell">
                <button type="button"
                    class="teacher-count-pill view-course-teachers-btn {{ $teacherCount ? 'is-active' : 'is-empty' }}"
                    data-course-id="{{ $course->id }}"
                    data-course-name="{{ $course->name }}"
                    data-class-id="{{ $classId }}"
                    data-class-name="{{ $className }}"
                    data-url="{{ $courseUrl }}">
                    {{ $teacherCount }}
                </button>
            </td>
            <td>
                <button type="button" class="btn btn-sm btn-outline-primary-600 assign-course-teacher-btn"
                    data-course-id="{{ $course->id }}"
                    data-class-id="{{ $classId }}"
                    data-url="{{ $courseUrl }}">
                    <i class="ri-user-add-line"></i> Assign Teacher
                </button>
            </td>
        </tr>
    @endforeach
@endif
