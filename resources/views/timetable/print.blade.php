@extends('layouts.print')

@section('css')
<style>
    @page { size: A4 landscape; margin: 12mm; }
    .print-sheet { max-width: 1100px; }
    .tt-print { width: 100%; border-collapse: collapse; margin: 8px 40px 28px; width: calc(100% - 80px); }
    .tt-print th, .tt-print td {
        border: 1px solid #cbd5e1;
        padding: 8px 8px;
        font-size: 12px;
        vertical-align: top;
    }
    .tt-print th {
        background: #f0fdfa;
        color: #0f766e;
        font-size: 11px;
        letter-spacing: .04em;
        text-transform: uppercase;
    }
    .tt-print .time {
        width: 140px;
        font-weight: 700;
        background: #f8fafc;
        white-space: nowrap;
    }
    .tt-print .time small { display: block; font-weight: 500; color: #64748b; }
    .tt-print .kind {
        text-align: center;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .04em;
        color: #64748b;
        background: #f8fafc;
    }
    .tt-print .kind.break { background: #fffbeb; color: #b45309; }
    .tt-print .kind.assembly { background: #eef2ff; color: #4338ca; }
    .tt-print .subject { font-weight: 700; }
    .tt-print .teacher { display: block; color: #64748b; font-size: 11px; margin-top: 2px; }
    .tt-note { padding: 0 40px 32px; font-size: 12px; color: #64748b; }
    @media print {
        .tt-print th { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    }
</style>
@endsection

@section('content')
<div class="print-sheet" id="printSheet">
    <div class="print-toolbar no-print">
        <button type="button" onclick="window.print()" class="print-btn print-btn-secondary">
            <i class="ri-printer-line"></i> Print
        </button>
        <button type="button" onclick="window.close()" class="print-btn print-btn-primary">
            <i class="ri-close-line"></i> Close
        </button>
    </div>

    <header class="letterhead">
        <div class="letterhead-logo">
            <img src="{{ $logoUrl }}" alt="{{ $school->name ?: 'School' }}">
        </div>
        <div>
            <h2 class="letterhead-school">{{ $school->name ?: 'EasySchool' }}</h2>
            @if(!empty($school->motto))
                <p class="letterhead-motto">"{{ $school->motto }}"</p>
            @endif
            <div class="letterhead-meta">
                @if(!empty($school->address))
                    <span><i class="ri-map-pin-line"></i> {{ $school->address }}</span>
                @endif
                @if(!empty($school->phone))
                    <span><i class="ri-phone-line"></i> {{ $school->phone }}</span>
                @endif
            </div>
        </div>
        <div class="qr-block" style="min-width:132px;">
            <div style="font-size:11px;font-weight:800;letter-spacing:1px;text-transform:uppercase;color:var(--brand-dark);">Timetable</div>
            <div style="font-size:16px;font-weight:800;margin:6px 0 2px;">{{ $class->name }}</div>
            <div class="qr-block-label">{{ $period['year_name'] ?? '' }} {{ $period['term_name'] ?? '' }}</div>
        </div>
    </header>

    <div class="doc-head">
        <h1>{{ $class->name }} — class timetable</h1>
        <p>
            {{ $class->category?->name ?: 'Class' }}
            @if($class->classTeacher) &nbsp;&bull;&nbsp; Class teacher: {{ $class->classTeacher->full_name }} @endif
            @if(!empty($period['year_name'])) &nbsp;&bull;&nbsp; {{ $period['year_name'] }} · {{ $period['term_name'] }} @endif
        </p>
    </div>

    @if(! $timetable)
        <p class="tt-note">No timetable has been generated for this class in the selected period.</p>
    @else
        <table class="tt-print">
            <thead>
                <tr>
                    <th>Time</th>
                    @foreach($days as $dayName)
                        <th>{{ $dayName }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($periods as $slot)
                    <tr>
                        <td class="time">
                            {{ $slot->label }}
                            <small>{{ $slot->timeLabel() }}</small>
                        </td>
                        @foreach($days as $day => $dayName)
                            @if($slot->kind !== 'lesson')
                                <td class="kind {{ $slot->kind }}">{{ $slot->label }}</td>
                            @else
                                @php $entry = $grid[$day][$slot->id] ?? null; @endphp
                                <td>
                                    @if($entry?->course)
                                        <span class="subject">{{ $entry->course->name }}</span>
                                        @if($entry->teacher)
                                            <span class="teacher">{{ $entry->teacher->full_name }}</span>
                                        @endif
                                    @else
                                        —
                                    @endif
                                </td>
                            @endif
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
        <p class="tt-note">Set per class, Monday to Friday. Saved {{ $timetable->generated_at?->format('d M Y H:i') }}.</p>
    @endif
</div>
@endsection
