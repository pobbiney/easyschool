@php
    $courseOptionsForJs = collect($subjectAssignments ?? [])->map(function ($a) {
        return [
            'class_id' => (string) $a->school_class_id,
            'course_id' => (string) $a->course_id,
            'course_name' => $a->course?->name ?? 'Unknown subject',
        ];
    })->values();
    $defaultClassIdJs = isset($defaultClassId) ? (string) $defaultClassId : '';
    $defaultCourseIdJs = isset($defaultCourseId) ? (string) $defaultCourseId : '';
    $lockClass = (bool) ($lockClass ?? false);
    $lockCourse = (bool) ($lockCourse ?? false);

    $homeroomIds = collect($homeroomClasses ?? [])->pluck('id')->flip();
    $classOptions = collect();
    foreach ($homeroomClasses ?? [] as $class) {
        $classOptions->put($class->id, ['name' => $class->name, 'homeroom' => true]);
    }
    foreach ($subjectAssignments ?? [] as $a) {
        if ($a->schoolClass && ! $classOptions->has($a->school_class_id)) {
            $classOptions->put($a->school_class_id, [
                'name' => $a->schoolClass->name,
                'homeroom' => $homeroomIds->has($a->school_class_id),
            ]);
        }
    }
    if ($lockClass && isset($defaultClassId) && isset($schoolClass) && ! $classOptions->has($defaultClassId)) {
        $classOptions->put($defaultClassId, [
            'name' => $schoolClass->name,
            'homeroom' => $homeroomIds->has($defaultClassId),
        ]);
    }
@endphp

<div class="modal fade" id="createAssessmentModal" tabindex="-1"
    data-courses='@json($courseOptionsForJs)'
    data-default-class="{{ $defaultClassIdJs }}"
    data-default-course="{{ $defaultCourseIdJs }}"
    data-lock-class="{{ $lockClass ? '1' : '0' }}"
    data-lock-course="{{ $lockCourse ? '1' : '0' }}">
    <div class="modal-dialog modal-lg">
        <form action="{{ route('teacher-assessments-process') }}" method="POST" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title">Create Assessment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Type</label>
                        <select name="type" class="form-select" required>
                            @foreach(\App\Models\AcademicAssessment::TYPES as $type)
                                <option value="{{ $type }}">{{ ucwords(str_replace('_', ' ', $type)) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select" required>
                            @foreach(\App\Models\AcademicAssessment::STATUSES as $status)
                                <option value="{{ $status }}" @selected($status === 'published')>{{ ucfirst($status) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Title</label>
                        <input type="text" name="title" class="form-control" required maxlength="200">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Class</label>
                        @if($lockClass && $defaultClassIdJs)
                            <input type="hidden" name="school_class_id" value="{{ $defaultClassIdJs }}">
                            <select class="form-select" id="assessment_class_id" disabled>
                                @foreach($classOptions as $id => $meta)
                                    @if((string) $id === $defaultClassIdJs)
                                        <option value="{{ $id }}" selected>{{ $meta['name'] }}</option>
                                    @endif
                                @endforeach
                            </select>
                        @else
                            <select name="school_class_id" class="form-select" required id="assessment_class_id">
                                @foreach($classOptions as $id => $meta)
                                    <option value="{{ $id }}" @selected(($defaultClassId ?? null) == $id)>{{ $meta['name'] }}</option>
                                @endforeach
                            </select>
                        @endif
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Course</label>
                        @if($lockCourse && $defaultCourseIdJs)
                            <input type="hidden" name="course_id" value="{{ $defaultCourseIdJs }}">
                            <select class="form-select" id="assessment_course_id" disabled></select>
                        @else
                            <select name="course_id" class="form-select" required id="assessment_course_id"></select>
                        @endif
                        <div class="form-text" id="assessment_course_hint">Courses update when you change the class.</div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Max Score</label>
                        <input type="number" name="max_score" class="form-control" value="100" min="1" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Assessment Date</label>
                        <input type="date" name="assessment_date" class="form-control">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Due Date</label>
                        <input type="date" name="due_date" class="form-control">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="3"></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary-600">Create Assessment</button>
            </div>
        </form>
    </div>
</div>

<script>
(function () {
    function parseJsonAttr(el, name, fallback) {
        try {
            return JSON.parse(el.getAttribute(name) || fallback);
        } catch (e) {
            return JSON.parse(fallback);
        }
    }

    function initAssessmentClassCourseFilter() {
        const modal = document.getElementById('createAssessmentModal');
        const classSelect = document.getElementById('assessment_class_id');
        const courseSelect = document.getElementById('assessment_course_id');
        const hint = document.getElementById('assessment_course_hint');

        if (! modal || ! classSelect || ! courseSelect) {
            return;
        }

        const allCourses = parseJsonAttr(modal, 'data-courses', '[]');
        const defaultClass = modal.getAttribute('data-default-class') || '';
        const defaultCourse = modal.getAttribute('data-default-course') || '';
        const lockClass = modal.getAttribute('data-lock-class') === '1';
        const lockCourse = modal.getAttribute('data-lock-course') === '1';

        function currentClassId() {
            if (lockClass) {
                return defaultClass || classSelect.value;
            }
            return classSelect.value;
        }

        function coursesForClass(classId) {
            return allCourses.filter(function (c) {
                return c.class_id === String(classId);
            });
        }

        function rebuildCourseSelect(preferredCourseId) {
            const classId = currentClassId();
            const courses = coursesForClass(classId);

            if (lockCourse) {
                courseSelect.innerHTML = '';
                const locked = allCourses.find(function (c) {
                    return c.class_id === String(classId) && c.course_id === String(defaultCourse);
                });
                const opt = document.createElement('option');
                opt.value = defaultCourse;
                opt.textContent = locked ? locked.course_name : 'Selected subject';
                opt.selected = true;
                courseSelect.appendChild(opt);
                hint.textContent = 'Subject is fixed for this workspace.';
                return;
            }

            courseSelect.innerHTML = '';

            if (! classId) {
                const placeholder = document.createElement('option');
                placeholder.value = '';
                placeholder.textContent = 'Select a class first';
                placeholder.disabled = true;
                placeholder.selected = true;
                courseSelect.appendChild(placeholder);
                courseSelect.required = false;
                hint.textContent = 'Select a class first to see available courses.';
                return;
            }

            if (courses.length === 0) {
                const emptyOpt = document.createElement('option');
                emptyOpt.value = '';
                emptyOpt.textContent = '— No subjects —';
                emptyOpt.disabled = true;
                emptyOpt.selected = true;
                courseSelect.appendChild(emptyOpt);
                courseSelect.required = false;
                hint.textContent = 'No subject assignments for this class.';
                return;
            }

            courses.forEach(function (course) {
                const opt = document.createElement('option');
                opt.value = course.course_id;
                opt.textContent = course.course_name;
                courseSelect.appendChild(opt);
            });

            courseSelect.required = true;

            const pick = preferredCourseId || defaultCourse;
            const options = Array.from(courseSelect.options);

            if (pick && options.some(function (o) { return o.value === String(pick); })) {
                courseSelect.value = String(pick);
            } else {
                courseSelect.value = courses[0].course_id;
            }

            hint.textContent = courses.length + ' subject' + (courses.length === 1 ? '' : 's') + ' available for this class.';
        }

        if (! lockClass) {
            classSelect.addEventListener('change', function () {
                rebuildCourseSelect('');
            });
        }

        if (! modal.dataset.filterBound) {
            modal.dataset.filterBound = '1';
            modal.addEventListener('shown.bs.modal', function () {
                rebuildCourseSelect(defaultCourse);
            });
        }

        rebuildCourseSelect(defaultCourse);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initAssessmentClassCourseFilter);
    } else {
        initAssessmentClassCourseFilter();
    }
})();
</script>
