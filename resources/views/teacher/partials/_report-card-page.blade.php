<div class="report-card-page" @if(!empty($withPageBreak)) style="page-break-after: always;" @endif>
    <div class="doc-head">
        <div class="doc-brand">
            @if(!empty($school->logo_path))
                <img src="{{ asset($school->logo_path) }}" alt="" class="doc-logo">
            @endif
            <div>
                <h1>{{ $school->name ?? 'EasySchool' }}</h1>
                <p>Student Report Card</p>
            </div>
        </div>
        <div class="doc-meta">
            <div><strong>Printed:</strong> {{ $printedAt->format('d M Y') }}</div>
            @if(!empty($period['year_name']))
                <div><strong>Session:</strong> {{ $period['year_name'] }} / {{ $period['term_name'] }}</div>
            @endif
        </div>
    </div>

    <div class="doc-section">
        <h2>Student Information</h2>
        <table class="doc-table">
            <tr><th>Name</th><td>{{ $report['student']->full_name }}</td><th>Student ID</th><td>{{ $report['student']->student_id }}</td></tr>
            <tr><th>Class</th><td>{{ $report['student']->schoolClass?->name }}</td><th>Gender</th><td>{{ $report['student']->gender }}</td></tr>
        </table>
    </div>

    <div class="doc-section">
        <h2>Attendance Summary</h2>
        <table class="doc-table">
            <tr>
                <th>Present</th><td>{{ $report['attendance']['present'] }}</td>
                <th>Absent</th><td>{{ $report['attendance']['absent'] }}</td>
                <th>Late</th><td>{{ $report['attendance']['late'] }}</td>
                <th>Excused</th><td>{{ $report['attendance']['excused'] }}</td>
            </tr>
        </table>
    </div>

    <div class="doc-section">
        <h2>Subject Performance</h2>
        <table class="doc-table">
            <thead>
                <tr>
                    <th>Subject</th>
                    <th>Average</th>
                    <th>Grade</th>
                </tr>
            </thead>
            <tbody>
                @foreach($report['subject_grades'] as $subject)
                <tr>
                    <td>{{ $subject['course_name'] }}</td>
                    <td>{{ $subject['average_percentage'] !== null ? number_format($subject['average_percentage'], 1).'%' : '—' }}</td>
                    <td>{{ $subject['letter_grade'] ?? '—' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="doc-section">
        <h2>Term Summary</h2>
        <table class="doc-table">
            <tr>
                <th>Overall Average</th>
                <td>{{ $report['term_average']['average_percentage'] !== null ? number_format($report['term_average']['average_percentage'], 1).'%' : '—' }}</td>
                <th>Overall Grade</th>
                <td>{{ $report['term_average']['letter_grade'] ?? '—' }}</td>
            </tr>
        </table>
    </div>
</div>
