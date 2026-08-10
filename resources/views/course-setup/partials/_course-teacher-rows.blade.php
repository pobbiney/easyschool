@php
    $assignments = $course->teachingAssignments;
    $courseLabel = $isSubCourse ? 'Sub-Course' : 'Course';
    $rowClass = $isSubCourse ? 'subcourse-row' : 'course-group-row';
    $initials = strtoupper(substr($course->name, 0, 2));
@endphp

@if($assignments->isEmpty())
    <tr class="{{ $rowClass }}">
        <td>
            <div class="course-name-cell">
                <span class="course-avatar">{{ $isSubCourse ? '↳' : $initials }}</span>
                <span class="fw-semibold {{ $isSubCourse ? '' : 'text-primary-600' }}">{{ $course->name }}</span>
            </div>
        </td>
        <td><span class="type-badge">{{ $courseLabel }}</span></td>
        <td><span class="text-secondary-light">—</span></td>
        <td><span class="teacher-empty-pill"><i class="ri-user-unfollow-line"></i> Not assigned</span></td>
        <td><span class="text-secondary-light">—</span></td>
        <td>
            <button type="button" class="btn btn-sm btn-outline-primary-600 assign-course-teacher-btn"
                data-course-id="{{ $course->id }}"
                data-course-name="{{ $course->name }}"
                data-course-type="{{ $courseLabel }}"
                data-url="{{ route('get-course-teacher-assignment', $course->id) }}">
                <i class="ri-user-shared-line"></i> Assign Teacher
            </button>
        </td>
    </tr>
@else
    @foreach($assignments as $assignment)
        @php $teacher = $assignment->teacher; @endphp
        <tr class="{{ $rowClass }}">
            <td>
                <div class="course-name-cell">
                    <span class="course-avatar">{{ $isSubCourse ? '↳' : $initials }}</span>
                    <span class="fw-semibold {{ $isSubCourse ? '' : 'text-primary-600' }}">{{ $course->name }}</span>
                </div>
            </td>
            <td><span class="type-badge">{{ $courseLabel }}</span></td>
            <td><span class="class-badge">{{ $assignment->schoolClass?->name ?? '—' }}</span></td>
            <td>
                @if($teacher)
                    <div class="teacher-name-cell">
                        <span class="teacher-avatar">
                            @if($teacher->picture)
                                <img src="{{ asset($teacher->picture) }}" alt="{{ $teacher->full_name }}">
                            @else
                                {{ strtoupper(substr($teacher->firstname, 0, 1) . substr($teacher->surname, 0, 1)) }}
                            @endif
                        </span>
                        <span class="fw-semibold">{{ $teacher->full_name }}</span>
                    </div>
                @else
                    <span class="teacher-empty-pill">Not assigned</span>
                @endif
            </td>
            <td>
                @if($teacher)
                    <span class="position-badge">{{ $teacher->position }}</span>
                @else
                    <span class="text-secondary-light">—</span>
                @endif
            </td>
            <td>
                <div class="d-flex flex-wrap gap-2">
                    <button type="button" class="btn btn-sm btn-outline-primary-600 assign-course-teacher-btn"
                        data-course-id="{{ $course->id }}"
                        data-course-name="{{ $course->name }}"
                        data-course-type="{{ $courseLabel }}"
                        data-class-id="{{ $assignment->school_class_id }}"
                        data-url="{{ route('get-course-teacher-assignment', $course->id) }}">
                        <i class="ri-edit-2-line"></i> Change
                    </button>
                    <form method="POST" action="{{ route('unassign-course-teacher-process') }}" class="d-inline">
                        @csrf
                        <input type="hidden" name="assignment_id" value="{{ $assignment->id }}">
                        <button type="submit" class="btn btn-sm btn-outline-danger-600" onclick="return confirm('Remove this assignment?')">
                            Remove
                        </button>
                    </form>
                </div>
            </td>
        </tr>
    @endforeach
    <tr class="{{ $rowClass }}">
        <td colspan="5" class="text-secondary-light text-sm">Add another class assignment for {{ $course->name }}</td>
        <td>
            <button type="button" class="btn btn-sm btn-outline-neutral-400 assign-course-teacher-btn"
                data-course-id="{{ $course->id }}"
                data-course-name="{{ $course->name }}"
                data-course-type="{{ $courseLabel }}"
                data-url="{{ route('get-course-teacher-assignment', $course->id) }}">
                <i class="ri-add-line"></i> Add Class
            </button>
        </td>
    </tr>
@endif
