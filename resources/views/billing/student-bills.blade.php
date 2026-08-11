@php $pageName = "bill-management"; $subpageName = "student-bills"; @endphp
@extends('layouts.app')
@section('css')
<style>
    .bill-stat-card{border:1px solid var(--neutral-200,#e5e7eb);border-radius:16px;padding:20px 22px;background:#fff;height:100%}
    .bill-list-wrapper{border:1px solid var(--neutral-200,#e5e7eb);border-radius:16px;overflow:hidden;box-shadow:0 1px 3px rgba(15,23,42,.04)}
    .status-pill{padding:4px 10px;border-radius:999px;font-size:12px;font-weight:600}
    .status-pill.Pending{background:rgba(245,158,11,.14);color:#b45309}
    .status-pill.Partial{background:rgba(59,130,246,.14);color:#1d4ed8}
    .status-pill.Paid{background:rgba(34,197,94,.14);color:#15803d}
    .status-pill.No-Bills,.status-pill.no-bills{background:var(--neutral-100,#f3f4f6);color:var(--neutral-500,#6b7280)}

    #billBreakdownModal .modal-content { border: none; overflow: hidden; box-shadow: 0 24px 48px rgba(15,23,42,.16); }
    #billBreakdownModal .modal-dialog { max-width: 720px; }

    .bbm-shell { background: #fff; }
    .bbm-hero {
        position: relative;
        padding: 24px 24px 20px;
        background: linear-gradient(135deg, #25A194 0%, #1d8a80 45%, #6366f1 100%);
        color: #fff;
        overflow: hidden;
    }
    .bbm-hero::before {
        content: '';
        position: absolute;
        top: -40px; right: -30px;
        width: 140px; height: 140px;
        border-radius: 50%;
        background: rgba(255,255,255,.08);
    }
    .bbm-hero::after {
        content: '';
        position: absolute;
        bottom: -50px; left: 20%;
        width: 180px; height: 180px;
        border-radius: 50%;
        background: rgba(255,255,255,.05);
    }
    .bbm-hero-top { position: relative; z-index: 1; display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; }
    .bbm-hero-left { display: flex; align-items: center; gap: 14px; min-width: 0; }
    .bbm-avatar {
        width: 52px; height: 52px; border-radius: 14px; flex-shrink: 0;
        display: inline-flex; align-items: center; justify-content: center;
        font-size: 18px; font-weight: 800; letter-spacing: .02em;
        background: rgba(255,255,255,.18); border: 2px solid rgba(255,255,255,.28);
        backdrop-filter: blur(4px);
    }
    .bbm-title { font-size: 18px; font-weight: 700; margin: 0 0 4px; letter-spacing: -.01em; }
    .bbm-subtitle { font-size: 12px; opacity: .88; margin: 0; }
    .bbm-close {
        width: 34px; height: 34px; border-radius: 10px; border: none; flex-shrink: 0;
        background: rgba(255,255,255,.16); color: #fff; font-size: 18px; line-height: 1;
        display: inline-flex; align-items: center; justify-content: center;
        transition: background .15s;
    }
    .bbm-close:hover { background: rgba(255,255,255,.28); color: #fff; }

    .bbm-chips { position: relative; z-index: 1; display: flex; flex-wrap: wrap; gap: 8px; margin-top: 16px; }
    .bbm-chip {
        display: inline-flex; align-items: center; gap: 6px;
        padding: 5px 11px; border-radius: 999px; font-size: 11px; font-weight: 600;
        background: rgba(255,255,255,.14); border: 1px solid rgba(255,255,255,.2);
    }
    .bbm-chip i { font-size: 13px; opacity: .9; }

    .bbm-stats {
        display: grid; grid-template-columns: repeat(3, 1fr);
        gap: 10px; padding: 16px 20px 0;
        margin-top: -12px; position: relative; z-index: 2;
    }
    @media (max-width: 576px) { .bbm-stats { grid-template-columns: 1fr; margin-top: 0; padding-top: 14px; } }
    .bbm-stat {
        border-radius: 14px; padding: 14px 16px; text-align: center;
        border: 1px solid #e5e7eb; background: #fff;
        box-shadow: 0 4px 14px rgba(15,23,42,.06);
    }
    .bbm-stat .lbl { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; color: #9ca3af; margin-bottom: 4px; }
    .bbm-stat .val { font-size: 17px; font-weight: 800; color: #111827; }
    .bbm-stat.due .val { color: #6366f1; }
    .bbm-stat.paid .val { color: #15803d; }
    .bbm-stat.balance .val { color: #dc2626; }

    .bbm-progress-wrap { padding: 14px 20px 0; }
    .bbm-progress-label { display: flex; justify-content: space-between; font-size: 11px; font-weight: 600; color: #6b7280; margin-bottom: 6px; }
    .bbm-progress-label span:last-child { color: #25A194; }
    .bbm-progress { height: 8px; border-radius: 999px; background: #f3f4f6; overflow: hidden; }
    .bbm-progress-bar {
        height: 100%; border-radius: 999px;
        background: linear-gradient(90deg, #25A194, #6366f1);
        transition: width .4s ease;
    }

    .bbm-body { padding: 16px 20px 20px; max-height: 380px; overflow-y: auto; }
    .bbm-section-title {
        font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .06em;
        color: #9ca3af; margin-bottom: 10px; display: flex; align-items: center; gap: 8px;
    }
    .bbm-section-title::after { content: ''; flex: 1; height: 1px; background: #f3f4f6; }

    .bbm-bill {
        display: flex; align-items: center; gap: 12px;
        padding: 13px 14px; border-radius: 13px; margin-bottom: 8px;
        border: 1px solid #f3f4f6; background: #fafafa;
        transition: transform .12s, box-shadow .12s, border-color .12s;
    }
    .bbm-bill:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(15,23,42,.06); border-color: #e5e7eb; background: #fff; }
    .bbm-bill.is-paid { border-left: 4px solid #22c55e; }
    .bbm-bill.is-partial { border-left: 4px solid #3b82f6; }
    .bbm-bill.is-pending { border-left: 4px solid #f59e0b; }

    .bbm-bill-icon {
        width: 40px; height: 40px; border-radius: 11px; flex-shrink: 0;
        display: inline-flex; align-items: center; justify-content: center; font-size: 18px;
    }
    .bbm-bill.is-paid .bbm-bill-icon { background: rgba(34,197,94,.12); color: #15803d; }
    .bbm-bill.is-partial .bbm-bill-icon { background: rgba(59,130,246,.12); color: #1d4ed8; }
    .bbm-bill.is-pending .bbm-bill-icon { background: rgba(245,158,11,.12); color: #b45309; }

    .bbm-bill-info { flex: 1; min-width: 0; }
    .bbm-bill-name { font-size: 13px; font-weight: 700; color: #111827; margin-bottom: 2px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .bbm-bill-meta { font-size: 11px; color: #9ca3af; }

    .bbm-bill-right { text-align: right; flex-shrink: 0; }
    .bbm-bill-balance { font-size: 14px; font-weight: 800; color: #111827; }
    .bbm-bill-sub { font-size: 10px; color: #9ca3af; margin-top: 2px; }

    .bbm-status {
        display: inline-flex; align-items: center; gap: 4px;
        padding: 3px 9px; border-radius: 999px; font-size: 10px; font-weight: 700;
        margin-top: 4px;
    }
    .bbm-status.Paid { background: rgba(34,197,94,.12); color: #15803d; }
    .bbm-status.Partial { background: rgba(59,130,246,.12); color: #1d4ed8; }
    .bbm-status.Pending { background: rgba(245,158,11,.12); color: #b45309; }

    .bbm-footer {
        padding: 14px 20px 18px; border-top: 1px solid #f3f4f6;
        background: linear-gradient(180deg, #fafafa, #fff);
        display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap;
    }
    .bbm-footer-label { font-size: 12px; color: #6b7280; font-weight: 600; }
    .bbm-footer-total { font-size: 20px; font-weight: 800; color: #dc2626; letter-spacing: -.02em; }
    .bbm-footer-total.is-clear { color: #15803d; }

    .bbm-loading { padding: 48px 24px; text-align: center; }
    .bbm-spinner {
        width: 44px; height: 44px; border-radius: 50%; margin: 0 auto 14px;
        border: 3px solid rgba(37,161,148,.15); border-top-color: #25A194;
        animation: bbmSpin .7s linear infinite;
    }
    @keyframes bbmSpin { to { transform: rotate(360deg); } }
    .bbm-loading-text { font-size: 13px; color: #9ca3af; font-weight: 600; }

    .bbm-empty { text-align: center; padding: 40px 20px; }
    .bbm-empty-icon {
        width: 56px; height: 56px; border-radius: 50%; margin: 0 auto 12px;
        display: flex; align-items: center; justify-content: center;
        background: rgba(99,102,241,.08); color: #6366f1; font-size: 26px;
    }
    .bbm-empty-title { font-size: 14px; font-weight: 700; color: #374151; margin-bottom: 4px; }
    .bbm-empty-sub { font-size: 12px; color: #9ca3af; margin: 0; }
</style>
@endsection
@section('content')
<div class="dashboard-main-body">
    <div class="breadcrumb d-flex flex-wrap align-items-center justify-content-between gap-3 mb-24">
        <div>
            <h1 class="fw-semibold mb-4 h6 text-primary-light">BILL MANAGEMENT</h1>
            <div><a href="{{ route('dashboard') }}" class="text-secondary-light hover-text-primary hover-underline">Dashboard</a><span class="text-secondary-light"> / Student Bills</span></div>
        </div>
        <a href="{{ route('category-bill-setup') }}" class="btn btn-outline-primary-600 d-flex align-items-center gap-6"><i class="ri-settings-3-line"></i> Category Bill Setup</a>
    </div>

    <div class="row gy-4 mb-24">
        <div class="col-sm-6 col-xl-3"><div class="bill-stat-card"><p class="text-sm text-secondary-light mb-4">Students</p><h4 class="fw-semibold mb-0">{{ $stats['students'] }}</h4></div></div>
        <div class="col-sm-6 col-xl-3"><div class="bill-stat-card"><p class="text-sm text-secondary-light mb-4">Total Due</p><h4 class="fw-semibold mb-0">{{ number_format($stats['total_due'], 2) }}</h4></div></div>
        <div class="col-sm-6 col-xl-3"><div class="bill-stat-card"><p class="text-sm text-secondary-light mb-4">Total Paid</p><h4 class="fw-semibold mb-0 text-success-600">{{ number_format($stats['total_paid'], 2) }}</h4></div></div>
        <div class="col-sm-6 col-xl-3"><div class="bill-stat-card"><p class="text-sm text-secondary-light mb-4">Outstanding</p><h4 class="fw-semibold mb-0 text-warning-600">{{ number_format($stats['outstanding'], 2) }}</h4></div></div>
    </div>

    <div class="card bill-list-wrapper mb-24">
        <div class="card-body p-24">
            <form method="GET" action="{{ route('student-bills') }}" class="row g-3">
                <div class="col-md-2"><select name="academic_term_id" class="form-select"><option value="">All terms</option>@foreach($academicTerms as $t)<option value="{{ $t->id }}" @selected(($filters['academic_term_id'] ?? '') == $t->id)>{{ $t->name }}</option>@endforeach</select></div>
                <div class="col-md-2"><select name="academic_year_id" class="form-select"><option value="">All years</option>@foreach($academicYears as $y)<option value="{{ $y->id }}" @selected(($filters['academic_year_id'] ?? '') == $y->id)>{{ $y->name }}</option>@endforeach</select></div>
                <div class="col-md-2"><select name="class_category_id" class="form-select"><option value="">All categories</option>@foreach($classCategories as $c)<option value="{{ $c->id }}" @selected(($filters['class_category_id'] ?? '') == $c->id)>{{ $c->name }}</option>@endforeach</select></div>
                <div class="col-md-2"><select name="school_class_id" class="form-select"><option value="">All classes</option>@foreach($schoolClasses as $c)<option value="{{ $c->id }}" @selected(($filters['school_class_id'] ?? '') == $c->id)>{{ $c->name }}</option>@endforeach</select></div>
                <div class="col-md-2"><select name="status" class="form-select"><option value="">All statuses</option>@foreach(['Pending','Partial','Paid','No Bills'] as $s)<option value="{{ $s }}" @selected(($filters['status'] ?? '') == $s)>{{ $s }}</option>@endforeach</select></div>
                <div class="col-md-2"><button type="submit" class="btn btn-primary-600 w-100">Filter</button></div>
            </form>
        </div>
    </div>

    <div class="card bill-list-wrapper">
        <div class="card-header border-bottom py-16 px-24 d-flex justify-content-between flex-wrap gap-3">
            <h6 class="text-lg fw-semibold mb-0">Student Bill Ledger</h6>
            <form class="navbar-search dt-search m-0"><input type="text" class="dt-input bg-transparent radius-4" aria-controls="dataTable" placeholder="Search students..."><iconify-icon icon="ion:search-outline" class="icon"></iconify-icon></form>
        </div>
        <div class="card-body p-0 dataTable-wrapper">
            @if($rows->isNotEmpty())
            <table class="table bordered-table mb-0 data-table" id="dataTable">
                <thead><tr><th>Student</th><th>Class</th><th>Category</th><th>Total Due</th><th>Paid</th><th>Balance</th><th>Status</th><th>Actions</th></tr></thead>
                <tbody>
                    @foreach($rows as $row)
                    <tr>
                        <td><span class="fw-semibold">{{ $row->student->full_name }}</span><br><small class="text-secondary-light">{{ $row->student->student_id }}</small></td>
                        <td>{{ $row->student->class_name }}</td>
                        <td>{{ $row->student->schoolClass?->category?->name ?: '—' }}</td>
                        <td>{{ number_format($row->total_due, 2) }}</td>
                        <td>{{ number_format($row->total_paid, 2) }}</td>
                        <td>{{ number_format($row->balance, 2) }}</td>
                        <td><span class="status-pill {{ $row->status === 'No Bills' ? 'no-bills' : $row->status }}">{{ $row->status }}</span></td>
                        <td class="d-flex flex-wrap gap-2">
                            <button type="button" class="btn btn-sm btn-outline-primary-600 view-bills-btn" data-id="{{ $row->student->id }}"><i class="ri-eye-line"></i></button>
                            @if($row->balance > 0)
                                <a href="{{ route('record-bill-payment', $row->student->id) }}" class="btn btn-sm btn-primary-600"><i class="ri-money-dollar-circle-line"></i></a>
                            @else
                                <button type="button" class="btn btn-sm btn-primary-600" disabled><i class="ri-money-dollar-circle-line"></i></button>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @else
            <div class="text-center py-56 px-24 text-secondary-light">No students match the selected filters.</div>
            @endif
        </div>
    </div>
</div>

<div class="modal fade" id="billBreakdownModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content radius-12">
            <div id="billBreakdownContent" class="bbm-shell">
                <div class="bbm-loading">
                    <div class="bbm-spinner"></div>
                    <p class="bbm-loading-text mb-0">Loading bill breakdown...</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@section('scripts')
<script>
    const billsUrl = @json(url('get-student-bills'));

    function bbmEsc(value) {
        return $('<div>').text(value ?? '').html();
    }

    function bbmMoney(value) {
        return parseFloat(value || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function bbmInitials(name) {
        const parts = (name || '').trim().split(/\s+/).filter(Boolean);
        if (!parts.length) return '?';
        if (parts.length === 1) return parts[0].charAt(0).toUpperCase();
        return (parts[0].charAt(0) + parts[parts.length - 1].charAt(0)).toUpperCase();
    }

    function bbmStatusClass(status) {
        if (status === 'Paid') return 'is-paid';
        if (status === 'Partial') return 'is-partial';
        return 'is-pending';
    }

    function bbmStatusIcon(status) {
        if (status === 'Paid') return 'ri-checkbox-circle-fill';
        if (status === 'Partial') return 'ri-time-fill';
        return 'ri-error-warning-fill';
    }

    function bbmLoadingHtml() {
        return '<div class="bbm-loading"><div class="bbm-spinner"></div><p class="bbm-loading-text mb-0">Loading bill breakdown...</p></div>';
    }

    function bbmRenderBreakdown(data) {
        const student = data.student || {};
        const summary = data.summary || {};
        const bills = data.bills || [];
        const totalDue = parseFloat(summary.total_due || 0);
        const totalPaid = parseFloat(summary.total_paid || 0);
        const balance = parseFloat(summary.balance || 0);
        const paidPct = totalDue > 0 ? Math.min(100, Math.round((totalPaid / totalDue) * 100)) : 0;
        const initials = bbmInitials(student.full_name);

        let billCards = '';
        if (bills.length) {
            billCards = bills.map(function(b) {
                const statusClass = bbmStatusClass(b.status);
                const period = [b.term_name, b.year_name].filter(Boolean).join(' · ') || '—';
                const category = b.category_name ? ' · ' + b.category_name : '';
                return (
                    '<div class="bbm-bill ' + statusClass + '">' +
                        '<div class="bbm-bill-icon"><i class="' + bbmStatusIcon(b.status) + '"></i></div>' +
                        '<div class="bbm-bill-info">' +
                            '<div class="bbm-bill-name">' + bbmEsc(b.item_name) + '</div>' +
                            '<div class="bbm-bill-meta">' + bbmEsc(period + category) + '</div>' +
                        '</div>' +
                        '<div class="bbm-bill-right">' +
                            '<div class="bbm-bill-balance">₵' + bbmMoney(b.balance) + '</div>' +
                            '<div class="bbm-bill-sub">Due ₵' + bbmMoney(b.amount_due) + ' · Paid ₵' + bbmMoney(b.amount_paid) + '</div>' +
                            '<span class="bbm-status ' + bbmEsc(b.status) + '">' + bbmEsc(b.status) + '</span>' +
                        '</div>' +
                    '</div>'
                );
            }).join('');
        } else {
            billCards = (
                '<div class="bbm-empty">' +
                    '<div class="bbm-empty-icon"><i class="ri-file-list-3-line"></i></div>' +
                    '<div class="bbm-empty-title">No bills found</div>' +
                    '<p class="bbm-empty-sub">This student has no bill items for the selected period.</p>' +
                '</div>'
            );
        }

        const chips = [
            student.class_name ? '<span class="bbm-chip"><i class="ri-book-open-line"></i>' + bbmEsc(student.class_name) + '</span>' : '',
            student.category_name ? '<span class="bbm-chip"><i class="ri-stack-line"></i>' + bbmEsc(student.category_name) + '</span>' : '',
            student.student_id ? '<span class="bbm-chip"><i class="ri-hashtag"></i>' + bbmEsc(student.student_id) + '</span>' : '',
        ].filter(Boolean).join('');

        return (
            '<div class="bbm-hero">' +
                '<div class="bbm-hero-top">' +
                    '<div class="bbm-hero-left">' +
                        '<div class="bbm-avatar">' + bbmEsc(initials) + '</div>' +
                        '<div>' +
                            '<h5 class="bbm-title">' + bbmEsc(student.full_name) + '</h5>' +
                            '<p class="bbm-subtitle">Bill breakdown overview</p>' +
                        '</div>' +
                    '</div>' +
                    '<button type="button" class="bbm-close" data-bs-dismiss="modal" aria-label="Close"><i class="ri-close-line"></i></button>' +
                '</div>' +
                (chips ? '<div class="bbm-chips">' + chips + '</div>' : '') +
            '</div>' +
            '<div class="bbm-stats">' +
                '<div class="bbm-stat due"><div class="lbl">Total Due</div><div class="val">₵' + bbmMoney(totalDue) + '</div></div>' +
                '<div class="bbm-stat paid"><div class="lbl">Total Paid</div><div class="val">₵' + bbmMoney(totalPaid) + '</div></div>' +
                '<div class="bbm-stat balance"><div class="lbl">Outstanding</div><div class="val">₵' + bbmMoney(balance) + '</div></div>' +
            '</div>' +
            (totalDue > 0 ? (
                '<div class="bbm-progress-wrap">' +
                    '<div class="bbm-progress-label"><span>Payment progress</span><span>' + paidPct + '% paid</span></div>' +
                    '<div class="bbm-progress"><div class="bbm-progress-bar" style="width:' + paidPct + '%"></div></div>' +
                '</div>'
            ) : '') +
            '<div class="bbm-body">' +
                '<div class="bbm-section-title">Bill items <span style="font-weight:600;color:#25A194;">(' + bills.length + ')</span></div>' +
                billCards +
            '</div>' +
            '<div class="bbm-footer">' +
                '<span class="bbm-footer-label">Amount still outstanding</span>' +
                '<span class="bbm-footer-total ' + (balance <= 0 ? 'is-clear' : '') + '">₵' + bbmMoney(balance) + '</span>' +
            '</div>'
        );
    }

    $('.view-bills-btn').on('click', function(){
        const id = $(this).data('id');
        $('#billBreakdownContent').html(bbmLoadingHtml());
        $('#billBreakdownModal').modal('show');
        $.get(billsUrl + '/' + id, function(data){
            $('#billBreakdownContent').html(bbmRenderBreakdown(data));
        }).fail(function(){
            $('#billBreakdownContent').html(
                '<div class="bbm-empty">' +
                    '<div class="bbm-empty-icon" style="background:rgba(220,38,38,.08);color:#dc2626;"><i class="ri-error-warning-line"></i></div>' +
                    '<div class="bbm-empty-title">Unable to load bills</div>' +
                    '<p class="bbm-empty-sub">Please try again in a moment.</p>' +
                '</div>'
            );
        });
    });
</script>
@endsection
