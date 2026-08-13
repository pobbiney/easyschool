@php $pageName = "teacher-portal"; $subpageName = "teacher-gradebook"; @endphp
@extends('layouts.app')

@section('css')
@include('partials._academic-ui-styles')
@endsection

@section('content')
<div class="dashboard-main-body">
    <div class="breadcrumb d-flex flex-wrap align-items-center justify-content-between gap-3 mb-24">
        <div>
            <h1 class="fw-semibold mb-4 h6 text-primary-light">TEACHER PORTAL</h1>
            <div>
                <a href="{{ route('teacher-dashboard') }}" class="text-secondary-light hover-text-primary hover-underline">Teacher Dashboard</a>
                <span class="text-secondary-light"> / Gradebook</span>
            </div>
        </div>
    </div>

    <div class="ac-hero d-flex align-items-start gap-16 mb-24">
        <span class="ac-avatar" style="width:52px;height:52px;font-size:22px;background:rgba(34,197,94,.1);color:#15803d;"><i class="ri-bar-chart-box-line"></i></span>
        <div>
            <h5 class="fw-semibold mb-6">Gradebook & Report Cards</h5>
            <p class="text-sm text-secondary-light mb-0">Term averages, subject breakdowns, and printable report cards for your homeroom classes.</p>
        </div>
    </div>

    <div class="row gy-4">
        @forelse($homeroomClasses as $class)
        @php $initials = strtoupper(substr($class->name, 0, 2)); @endphp
        <div class="col-md-4 col-sm-6">
            <div class="ac-workspace-card">
                <div class="card-top">
                    <div class="ac-name-cell">
                        <span class="ac-avatar">{{ $initials }}</span>
                        <div>
                            <span class="d-block fw-semibold text-primary-600">{{ $class->name }}</span>
                            <span class="ac-pill ac-pill-teal">Homeroom</span>
                        </div>
                    </div>
                </div>
                @if($period['year_name'])
                <p class="text-sm text-secondary-light mb-12"><span class="ac-pill ac-pill-indigo">{{ $period['year_name'] }} · {{ $period['term_name'] }}</span></p>
                @endif
                <div class="ac-action-pills">
                    <a href="{{ route('teacher-class-gradebook', $class) }}" class="ac-action-pill ac-action-pill-emerald"><i class="ri-bar-chart-box-line"></i> Gradebook</a>
                    <a href="{{ route('teacher-class-report-cards-print', $class) }}" target="_blank" class="ac-action-pill ac-action-pill-indigo"><i class="ri-printer-line"></i> Print All</a>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12">
            <div class="card ac-list-wrapper">
                <div class="card-body text-center py-56 px-24">
                    <span class="ac-pill ac-pill-emerald mb-12"><i class="ri-information-line"></i> Homeroom only</span>
                    <p class="text-secondary-light mb-0">Gradebook and report cards are available to homeroom class teachers.</p>
                </div>
            </div>
        </div>
        @endforelse
    </div>
</div>
@endsection
