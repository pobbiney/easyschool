@php
    $pageName = "teacher-portal";
    $subpageName = "teacher-gradebook";
    $gradePillClass = fn (?string $grade) => match (strtoupper(trim((string) $grade)[0] ?? '')) {
        'A' => 'ac-pill-grade-a', 'B' => 'ac-pill-grade-b', 'C' => 'ac-pill-grade-c',
        'D' => 'ac-pill-grade-d', 'F' => 'ac-pill-grade-f', default => 'ac-pill-slate',
    };
@endphp
@extends('layouts.app')

@section('css')
@include('partials._academic-ui-styles')
@endsection

@section('content')
<div class="dashboard-main-body">
    <div class="breadcrumb d-flex flex-wrap align-items-center justify-content-between gap-3 mb-24">
        <div>
            <h1 class="fw-semibold mb-4 h6 text-primary-light">GRADEBOOK</h1>
            <div>
                <a href="{{ route('teacher-gradebook') }}" class="text-secondary-light hover-text-primary hover-underline">Gradebook</a>
                <span class="text-secondary-light"> / {{ $schoolClass->name }}</span>
                @if($period['year_name'])
                    <span class="text-secondary-light"> · {{ $period['year_name'] }} / {{ $period['term_name'] }}</span>
                @endif
            </div>
        </div>
        <a href="{{ route('teacher-class-report-cards-print', $schoolClass) }}" target="_blank" class="btn btn-primary-600 d-flex align-items-center gap-6">
            <i class="ri-printer-line"></i> Print Report Cards
        </a>
    </div>

    <div class="ac-hero d-flex align-items-start gap-16 mb-24">
        <span class="ac-avatar" style="width:52px;height:52px;font-size:22px;background:rgba(34,197,94,.1);color:#15803d;">{{ strtoupper(substr($schoolClass->name, 0, 2)) }}</span>
        <div>
            <h5 class="fw-semibold mb-6">{{ $schoolClass->name }} — Term Gradebook</h5>
            <div class="d-flex flex-wrap gap-2">
                <span class="ac-pill ac-pill-teal">Homeroom</span>
                @if($period['year_name'])<span class="ac-pill ac-pill-indigo">{{ $period['year_name'] }} · {{ $period['term_name'] }}</span>@endif
                <span class="ac-pill ac-pill-emerald">{{ $gradebook['course_summaries']->count() }} subject{{ $gradebook['course_summaries']->count() === 1 ? '' : 's' }}</span>
            </div>
        </div>
    </div>

    @foreach($gradebook['course_summaries'] as $summary)
    <div class="card ac-list-wrapper mb-24">
        <div class="card-header border-bottom bg-base py-16 px-24">
            <h6 class="text-lg fw-semibold mb-4">{{ $summary['course_name'] }}</h6>
            <span class="ac-pill ac-pill-indigo">{{ $summary['assessments']->count() }} assessment{{ $summary['assessments']->count() === 1 ? '' : 's' }}</span>
        </div>
        <div class="card-body p-0 dataTable-wrapper">
            <div class="ac-list-scroll">
                <table class="table bordered-table mb-0">
                    <thead>
                        <tr>
                            <th>Student</th>
                            @foreach($summary['assessments'] as $assessment)
                                <th title="{{ $assessment->title }}">{{ Str::limit($assessment->title, 10) }}</th>
                            @endforeach
                            <th>Average</th>
                            <th>Grade</th>
                            <th>Report</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($summary['students'] as $row)
                        <tr>
                            <td class="fw-semibold">{{ $row['student']->full_name }}</td>
                            @foreach($summary['assessments'] as $assessment)
                                @php $score = $row['assessment_scores'][$assessment->id] ?? null; @endphp
                                <td>{{ $score?->score !== null ? number_format($score->score, 1) : '—' }}</td>
                            @endforeach
                            <td>
                                @if($row['average_percentage'] !== null)
                                    <span class="ac-pill ac-pill-violet">{{ number_format($row['average_percentage'], 1) }}%</span>
                                @else — @endif
                            </td>
                            <td>
                                @if($row['letter_grade'])
                                    <span class="ac-pill {{ $gradePillClass($row['letter_grade']) }}">{{ $row['letter_grade'] }}</span>
                                @else — @endif
                            </td>
                            <td>
                                <a href="{{ route('teacher-report-card-print', $row['student']) }}" target="_blank" class="ac-action-pill ac-action-pill-indigo"><i class="ri-printer-line"></i> Print</a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endforeach

    <div class="card ac-list-wrapper">
        <div class="card-header border-bottom bg-base py-16 px-24">
            <h6 class="text-lg fw-semibold mb-0">Term Averages — All Subjects</h6>
        </div>
        <div class="card-body p-0 dataTable-wrapper">
            <div class="ac-list-scroll">
                <table class="table bordered-table mb-0 data-table" id="dataTable">
                    <thead><tr><th>Student</th><th>Term Average</th><th>Letter Grade</th></tr></thead>
                    <tbody>
                        @foreach($gradebook['term_averages'] as $row)
                        <tr>
                            <td class="fw-semibold">{{ $row['student']->full_name }}</td>
                            <td>
                                @if($row['average_percentage'] !== null)
                                    <span class="ac-pill ac-pill-violet">{{ number_format($row['average_percentage'], 1) }}%</span>
                                @else — @endif
                            </td>
                            <td>
                                @if($row['letter_grade'])
                                    <span class="ac-pill {{ $gradePillClass($row['letter_grade']) }}">{{ $row['letter_grade'] }}</span>
                                @else — @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
