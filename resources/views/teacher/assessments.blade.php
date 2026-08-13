@php
    $pageName = "teacher-portal";
    $subpageName = "teacher-assessments";
    $typePillClass = fn (?string $type) => 'ac-pill-' . ($type ?: 'slate');
@endphp
@extends('layouts.app')

@section('css')
@include('partials._academic-ui-styles')
@endsection

@section('content')
<div class="dashboard-main-body">
    <div class="breadcrumb d-flex flex-wrap align-items-center justify-content-between gap-3 mb-24">
        <div>
            <h1 class="fw-semibold mb-4 h6 text-primary-light">ASSESSMENTS</h1>
            <div>
                <a href="{{ route('teacher-assessments') }}" class="text-secondary-light hover-text-primary hover-underline">Assessments</a>
                <span class="text-secondary-light"> / @if($course){{ $course->name }} / @endif{{ $schoolClass?->name }}</span>
            </div>
        </div>
        <button type="button" class="btn btn-primary-600 d-flex align-items-center gap-6" data-bs-toggle="modal" data-bs-target="#createAssessmentModal">
            <i class="ri-add-large-line"></i> New Assessment
        </button>
    </div>

    <div class="ac-hero d-flex align-items-start gap-16 mb-24">
        <span class="ac-avatar" style="width:52px;height:52px;font-size:22px;background:rgba(244,63,94,.1);color:#be123c;"><i class="ri-file-list-3-line"></i></span>
        <div>
            <h5 class="fw-semibold mb-6">@if($course){{ $course->name }} — @endif{{ $schoolClass?->name }}</h5>
            <div class="d-flex flex-wrap gap-2">
                @if($course)<span class="ac-pill ac-pill-indigo">{{ $course->name }}</span>@endif
                <span class="ac-pill ac-pill-teal">{{ $schoolClass?->name }}</span>
                <span class="ac-pill ac-pill-rose">{{ $assessments->count() }} assessment{{ $assessments->count() === 1 ? '' : 's' }}</span>
            </div>
        </div>
    </div>

    <div class="card ac-list-wrapper">
        <div class="card-header border-bottom bg-base py-16 px-24 d-flex justify-content-between flex-wrap gap-3">
            <h6 class="text-lg fw-semibold mb-0">All Assessments</h6>
            <form class="navbar-search dt-search m-0">
                <input type="text" class="dt-input bg-transparent radius-4" aria-controls="dataTable" placeholder="Search...">
                <iconify-icon icon="ion:search-outline" class="icon"></iconify-icon>
            </form>
        </div>
        <div class="card-body p-0 dataTable-wrapper">
            @if($assessments->isNotEmpty())
            <div class="ac-list-scroll">
                <table class="table bordered-table mb-0 data-table" id="dataTable">
                    <thead>
                        <tr><th>Title</th><th>Course</th><th>Type</th><th>Max</th><th>Scored</th><th>Status</th><th>Action</th></tr>
                    </thead>
                    <tbody>
                        @foreach($assessments as $assessment)
                        <tr>
                            <td class="fw-semibold">{{ $assessment->title }}</td>
                            <td><span class="ac-pill ac-pill-emerald">{{ $assessment->course?->name ?? 'Homeroom' }}</span></td>
                            <td><span class="ac-pill {{ $typePillClass($assessment->type) }}">{{ $assessment->typeLabel() }}</span></td>
                            <td>{{ number_format($assessment->max_score, 0) }}</td>
                            <td><span class="ac-pill ac-pill-violet">{{ $assessment->scores->whereNotNull('score')->count() }} scored</span></td>
                            <td><span class="ac-pill ac-pill-{{ $assessment->status === 'published' ? 'published' : 'draft' }}">{{ ucfirst($assessment->status) }}</span></td>
                            <td><a href="{{ route('teacher-assessment-scores', $assessment) }}" class="btn btn-sm btn-outline-primary-600"><i class="ri-edit-2-line"></i> Enter Scores</a></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="text-center py-56 px-24 text-secondary-light">No assessments for this workspace.</div>
            @endif
        </div>
    </div>
</div>

@include('teacher.partials._create-assessment-modal', [
    'homeroomClasses' => $homeroomClasses,
    'subjectAssignments' => $subjectAssignments,
    'defaultClassId' => $defaultClassId ?? $schoolClass?->id,
    'defaultCourseId' => $defaultCourseId ?? $course?->id,
    'lockClass' => $lockClass ?? false,
    'lockCourse' => isset($course),
    'schoolClass' => $schoolClass,
])
@endsection
