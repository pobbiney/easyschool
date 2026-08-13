@php
    $pageName = "teacher-portal";
    $subpageName = "teacher-assessments";
    $typePillClass = fn (?string $type) => 'ac-pill-' . ($type ?: 'slate');
    $typeIcon = fn (?string $type) => match ($type) {
        'homework' => 'ri-booklet-line',
        'class_test' => 'ri-file-edit-line',
        'exam' => 'ri-file-shield-2-line',
        'class_assignment' => 'ri-task-line',
        default => 'ri-file-list-3-line',
    };
@endphp
@extends('layouts.app')

@section('css')
@include('partials._academic-ui-styles')
<style>
    .ah-hero-actions { display: flex; flex-wrap: wrap; gap: 8px; align-items: center; }

    .ah-type-legend {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-top: 12px;
    }

    .ah-workspace-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 20px;
        margin-bottom: 24px;
    }
    @media (max-width: 991px) { .ah-workspace-grid { grid-template-columns: 1fr; } }

    .ah-panel {
        border: 1px solid var(--neutral-200, #e5e7eb);
        border-radius: 16px;
        background: #fff;
        overflow: hidden;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
        height: 100%;
    }

    .ah-panel-head {
        padding: 18px 22px;
        border-bottom: 1px solid var(--neutral-200, #e5e7eb);
        background: linear-gradient(180deg, #fafafa, #fff);
    }

    .ah-panel-body { padding: 16px 18px 20px; }

    .ah-slot-card {
        border: 1px solid #eef2f7;
        border-radius: 14px;
        padding: 14px 16px;
        margin-bottom: 12px;
        transition: box-shadow 0.15s ease, border-color 0.15s ease;
        background: #fff;
    }

    .ah-slot-card:hover {
        border-color: rgba(37, 161, 148, 0.25);
        box-shadow: 0 8px 22px rgba(15, 23, 42, 0.06);
    }

    .ah-slot-card:last-child { margin-bottom: 0; }

    .ah-slot-top {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 12px;
    }

    .ah-empty-slot {
        text-align: center;
        padding: 32px 20px;
        border: 2px dashed #e5e7eb;
        border-radius: 14px;
        background: #fafafa;
    }

    .ah-empty-slot i { font-size: 28px; color: #9ca3af; margin-bottom: 8px; display: block; }

    .ah-assessment-panel .table thead th {
        background: #f9fafb;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .05em;
        color: #6b7280;
        padding: 14px 16px;
        white-space: nowrap;
        border-bottom: 1px solid #e5e7eb;
    }

    .ah-assessment-panel .table tbody td {
        padding: 14px 16px;
        vertical-align: middle;
        border-bottom: 1px solid #f3f4f6;
    }

    .ah-assessment-panel .table tbody tr:hover td {
        background: rgba(37, 161, 148, 0.03);
    }

    .ah-assessment-title {
        display: flex;
        align-items: center;
        gap: 12px;
        min-width: 200px;
    }

    .ah-assessment-icon {
        width: 40px;
        height: 40px;
        border-radius: 11px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        flex-shrink: 0;
    }

    .ah-assessment-icon.homework { background: rgba(14, 165, 233, 0.12); color: #0369a1; }
    .ah-assessment-icon.class_test { background: rgba(139, 92, 246, 0.12); color: #6d28d9; }
    .ah-assessment-icon.exam { background: rgba(244, 63, 94, 0.12); color: #be123c; }
    .ah-assessment-icon.class_assignment { background: rgba(234, 88, 12, 0.12); color: #c2410c; }

    .ah-assessment-meta {
        font-size: 11px;
        color: #9ca3af;
        margin-top: 2px;
    }

    .ah-toolbar {
        padding: 14px 22px;
        border-bottom: 1px solid #e5e7eb;
        background: #fff;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        flex-wrap: wrap;
    }

    .ah-filter-pills { display: flex; flex-wrap: wrap; gap: 8px; }

    .ah-filter-pill {
        border: 1px solid #e5e7eb;
        background: #fff;
        color: #64748b;
        padding: 6px 14px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.15s ease;
    }

    .ah-filter-pill:hover,
    .ah-filter-pill.is-active {
        border-color: rgba(37, 161, 148, 0.35);
        background: rgba(37, 161, 148, 0.08);
        color: #1a7a70;
    }

    .ah-empty-assessments {
        text-align: center;
        padding: 56px 24px;
    }

    .ah-empty-assessments .ac-avatar {
        width: 64px;
        height: 64px;
        font-size: 28px;
        margin: 0 auto 16px;
        background: rgba(244, 63, 94, 0.1);
        color: #be123c;
    }
</style>
@endsection

@section('content')
<div class="dashboard-main-body">
    <div class="breadcrumb d-flex flex-wrap align-items-center justify-content-between gap-3 mb-24">
        <div>
            <h1 class="fw-semibold mb-4 h6 text-primary-light">TEACHER PORTAL</h1>
            <div>
                <a href="{{ route('teacher-dashboard') }}" class="text-secondary-light hover-text-primary hover-underline">Teacher Dashboard</a>
                <span class="text-secondary-light"> / Assessments</span>
            </div>
        </div>
        <div class="ah-hero-actions">
            @if($period['year_name'])
                <span class="ac-pill ac-pill-indigo"><i class="ri-calendar-line"></i> {{ $period['year_name'] }} · {{ $period['term_name'] }}</span>
            @endif
            <button type="button" class="btn btn-primary-600 d-flex align-items-center gap-6" data-bs-toggle="modal" data-bs-target="#createAssessmentModal">
                <i class="ri-add-large-line"></i> New Assessment
            </button>
        </div>
    </div>

    <div class="ac-hero d-flex align-items-start justify-content-between gap-16 mb-24 flex-wrap">
        <div class="d-flex align-items-start gap-16">
            <span class="ac-avatar" style="width:56px;height:56px;font-size:24px;background:rgba(244,63,94,.1);color:#be123c;"><i class="ri-file-list-3-line"></i></span>
            <div>
                <h5 class="fw-semibold mb-8">Assessments Hub</h5>
                <p class="text-sm text-secondary-light mb-0">Create homework, class tests, exams, and assignments — then enter scores from one place.</p>
                <div class="ah-type-legend">
                    <span class="ac-pill ac-pill-homework"><i class="ri-booklet-line"></i> Homework</span>
                    <span class="ac-pill ac-pill-class_test"><i class="ri-file-edit-line"></i> Class Test</span>
                    <span class="ac-pill ac-pill-exam"><i class="ri-file-shield-2-line"></i> Exam</span>
                    <span class="ac-pill ac-pill-class_assignment"><i class="ri-task-line"></i> Assignment</span>
                </div>
            </div>
        </div>
    </div>

    <div class="row gy-4 mb-24">
        <div class="col-sm-6 col-xl-3">
            <div class="ac-stat-card">
                <div class="d-flex align-items-center justify-content-between gap-3">
                    <div>
                        <p class="text-secondary-light text-sm mb-4">Total Assessments</p>
                        <h4 class="fw-semibold mb-0">{{ $stats['total'] }}</h4>
                    </div>
                    <span class="stat-icon bg-primary-50 text-primary-600"><i class="ri-file-list-3-line"></i></span>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="ac-stat-card">
                <div class="d-flex align-items-center justify-content-between gap-3">
                    <div>
                        <p class="text-secondary-light text-sm mb-4">Published</p>
                        <h4 class="fw-semibold mb-0 text-success-600">{{ $stats['published'] }}</h4>
                    </div>
                    <span class="stat-icon bg-success-100 text-success-600"><i class="ri-checkbox-circle-line"></i></span>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="ac-stat-card">
                <div class="d-flex align-items-center justify-content-between gap-3">
                    <div>
                        <p class="text-secondary-light text-sm mb-4">Drafts</p>
                        <h4 class="fw-semibold mb-0 text-warning-600">{{ $stats['draft'] }}</h4>
                    </div>
                    <span class="stat-icon bg-warning-100 text-warning-600"><i class="ri-draft-line"></i></span>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="ac-stat-card">
                <div class="d-flex align-items-center justify-content-between gap-3">
                    <div>
                        <p class="text-secondary-light text-sm mb-4">Scores Entered</p>
                        <h4 class="fw-semibold mb-0 text-info-600">{{ $stats['scores_entered'] }}</h4>
                    </div>
                    <span class="stat-icon bg-info-100 text-info-600"><i class="ri-edit-box-line"></i></span>
                </div>
            </div>
        </div>
    </div>

    <div class="ah-workspace-grid">
        <div class="ah-panel">
            <div class="ah-panel-head">
                <div class="d-flex align-items-center justify-content-between gap-3 flex-wrap">
                    <div>
                        <h6 class="text-lg fw-semibold mb-4">Homeroom Classes</h6>
                        <span class="ac-pill ac-pill-teal"><i class="ri-home-smile-line"></i> Class teacher · {{ $stats['homeroom_slots'] }}</span>
                    </div>
                </div>
            </div>
            <div class="ah-panel-body">
                @forelse($homeroomClasses as $class)
                @php $initials = strtoupper(substr($class->name, 0, 2)); @endphp
                <div class="ah-slot-card">
                    <div class="ah-slot-top">
                        <div class="ac-name-cell">
                            <span class="ac-avatar">{{ $initials }}</span>
                            <div>
                                <span class="fw-semibold d-block text-primary-600">{{ $class->name }}</span>
                                <span class="ac-pill ac-pill-teal">Homeroom</span>
                            </div>
                        </div>
                        <a href="{{ route('teacher-class-assessments', $class) }}" class="ac-action-pill ac-action-pill-teal">
                            <i class="ri-arrow-right-line"></i> Open
                        </a>
                    </div>
                </div>
                @empty
                <div class="ah-empty-slot">
                    <i class="ri-home-smile-line"></i>
                    <p class="text-secondary-light mb-0">No homeroom class assigned.</p>
                </div>
                @endforelse
            </div>
        </div>

        <div class="ah-panel">
            <div class="ah-panel-head">
                <div class="d-flex align-items-center justify-content-between gap-3 flex-wrap">
                    <div>
                        <h6 class="text-lg fw-semibold mb-4">Subject Assignments</h6>
                        <span class="ac-pill ac-pill-indigo"><i class="ri-book-open-line"></i> Course teacher · {{ $stats['subject_slots'] }}</span>
                    </div>
                </div>
            </div>
            <div class="ah-panel-body">
                @forelse($subjectAssignments as $assignment)
                <div class="ah-slot-card">
                    <div class="ah-slot-top">
                        <div>
                            <span class="fw-semibold d-block">{{ $assignment->course?->name }}</span>
                            <span class="ac-pill ac-pill-indigo"><i class="ri-group-line"></i> {{ $assignment->schoolClass?->name }}</span>
                        </div>
                        <a href="{{ route('teacher-course-assessments', [$assignment->course_id, $assignment->school_class_id]) }}" class="ac-action-pill ac-action-pill-indigo">
                            <i class="ri-arrow-right-line"></i> Open
                        </a>
                    </div>
                </div>
                @empty
                <div class="ah-empty-slot">
                    <i class="ri-book-open-line"></i>
                    <p class="text-secondary-light mb-0">No subject assignments this term.</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>

    <div class="ah-panel ah-assessment-panel">
        <div class="ah-panel-head d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div>
                <h6 class="text-lg fw-semibold mb-4">Recent Assessments</h6>
                <p class="text-sm text-secondary-light mb-0">All assessments you created for the current term.</p>
            </div>
        </div>

        @if($assessments->isNotEmpty())
        <div class="ah-toolbar">
            <div class="ah-filter-pills">
                <button type="button" class="ah-filter-pill is-active" data-filter="all">All</button>
                <button type="button" class="ah-filter-pill" data-filter="homework">Homework</button>
                <button type="button" class="ah-filter-pill" data-filter="class_test">Class Test</button>
                <button type="button" class="ah-filter-pill" data-filter="exam">Exam</button>
                <button type="button" class="ah-filter-pill" data-filter="class_assignment">Assignment</button>
            </div>
            <input type="text" id="ahSearch" class="form-control radius-4" placeholder="Search assessments..." style="min-width:220px;max-width:280px;">
        </div>
        <div class="ac-list-scroll dataTable-wrapper">
            <table class="table mb-0 data-table" id="dataTable">
                <thead>
                    <tr>
                        <th>Assessment</th>
                        <th>Type</th>
                        <th>Class</th>
                        <th>Subject</th>
                        <th>Max</th>
                        <th>Scored</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($assessments as $assessment)
                    @php
                        $scored = $assessment->scores->whereNotNull('score')->count();
                        $searchText = strtolower($assessment->title . ' ' . ($assessment->schoolClass?->name ?? '') . ' ' . ($assessment->course?->name ?? ''));
                    @endphp
                    <tr class="ah-assessment-row" data-type="{{ $assessment->type }}" data-search="{{ $searchText }}">
                        <td>
                            <div class="ah-assessment-title">
                                <span class="ah-assessment-icon {{ $assessment->type }}"><i class="{{ $typeIcon($assessment->type) }}"></i></span>
                                <div>
                                    <span class="fw-semibold d-block">{{ $assessment->title }}</span>
                                    <span class="ah-assessment-meta">
                                        @if($assessment->assessment_date)
                                            {{ $assessment->assessment_date->format('d M Y') }}
                                        @else
                                            No date set
                                        @endif
                                        @if($assessment->due_date)
                                            · Due {{ $assessment->due_date->format('d M') }}
                                        @endif
                                    </span>
                                </div>
                            </div>
                        </td>
                        <td><span class="ac-pill {{ $typePillClass($assessment->type) }}">{{ $assessment->typeLabel() }}</span></td>
                        <td><span class="ac-pill ac-pill-teal">{{ $assessment->schoolClass?->name }}</span></td>
                        <td><span class="ac-pill ac-pill-indigo">{{ $assessment->course?->name ?? 'Homeroom' }}</span></td>
                        <td><span class="ac-pill ac-pill-violet">{{ number_format($assessment->max_score, 0) }} pts</span></td>
                        <td><span class="ac-pill ac-pill-slate">{{ $scored }} scored</span></td>
                        <td><span class="ac-pill ac-pill-{{ $assessment->status === 'published' ? 'published' : 'draft' }}">{{ ucfirst($assessment->status) }}</span></td>
                        <td>
                            <a href="{{ route('teacher-assessment-scores', $assessment) }}" class="ac-action-pill ac-action-pill-rose">
                                <i class="ri-edit-2-line"></i> Enter Scores
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="ah-empty-assessments">
            <span class="ac-avatar d-flex align-items-center justify-content-center"><i class="ri-file-add-line"></i></span>
            <h6 class="fw-semibold mb-8">No assessments yet</h6>
            <p class="text-secondary-light mb-20">Create your first homework, test, or exam for a class you teach.</p>
            <button type="button" class="btn btn-primary-600" data-bs-toggle="modal" data-bs-target="#createAssessmentModal">
                <i class="ri-add-line"></i> Create Assessment
            </button>
        </div>
        @endif
    </div>
</div>

@include('teacher.partials._create-assessment-modal', [
    'homeroomClasses' => $homeroomClasses,
    'subjectAssignments' => $subjectAssignments,
])
@endsection

@section('scripts')
<script>
(function () {
    const rows = document.querySelectorAll('.ah-assessment-row');
    const pills = document.querySelectorAll('.ah-filter-pill');
    const search = document.getElementById('ahSearch');
    let activeFilter = 'all';

    function applyFilters() {
        const q = (search?.value || '').trim().toLowerCase();
        rows.forEach(row => {
            const typeMatch = activeFilter === 'all' || row.dataset.type === activeFilter;
            const searchMatch = !q || (row.dataset.search || '').includes(q);
            row.style.display = typeMatch && searchMatch ? '' : 'none';
        });
    }

    pills.forEach(pill => {
        pill.addEventListener('click', function () {
            pills.forEach(p => p.classList.remove('is-active'));
            this.classList.add('is-active');
            activeFilter = this.dataset.filter;
            applyFilters();
        });
    });

    search?.addEventListener('input', applyFilters);
})();
</script>
@endsection
