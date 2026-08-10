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
                            <button type="button" class="btn btn-sm btn-primary-600 record-payment-btn" data-id="{{ $row->student->id }}" @if($row->balance <= 0) disabled @endif><i class="ri-money-dollar-circle-line"></i></button>
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

<div class="modal fade" id="billBreakdownModal" tabindex="-1"><div class="modal-dialog modal-lg modal-dialog-centered"><div class="modal-content radius-12">
    <div class="modal-header border-bottom"><h6 class="modal-title fw-semibold">Bill Breakdown</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body"><div id="billBreakdownContent"><p class="text-secondary-light mb-0">Loading...</p></div></div>
</div></div></div>

<div class="modal fade" id="recordPaymentModal" tabindex="-1"><div class="modal-dialog modal-dialog-centered"><div class="modal-content radius-12">
    <div class="modal-header border-bottom"><h6 class="modal-title fw-semibold">Record Payment</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <form id="recordPaymentForm"><div class="modal-body">
        @csrf
        <input type="hidden" name="student_id" id="payment_student_id">
        <p class="text-sm text-secondary-light mb-16" id="paymentStudentLabel"></p>
        <div class="mb-12"><label class="form-label text-sm fw-medium">Amount</label><input type="number" min="0.01" step="0.01" name="amount" id="payment_amount" class="form-control" required></div>
        <div class="mb-12"><label class="form-label text-sm fw-medium">Payment Method</label><select name="payment_method" class="form-select" required><option value="Cash">Cash</option><option value="Mobile Money">Mobile Money</option><option value="Bank">Bank</option><option value="Cheque">Cheque</option></select></div>
        <div class="mb-12"><label class="form-label text-sm fw-medium">Reference</label><input type="text" name="reference" class="form-control" placeholder="Optional"></div>
        <div class="mb-12"><label class="form-label text-sm fw-medium">Payment Date</label><input type="datetime-local" name="paid_at" class="form-control" value="{{ now()->format('Y-m-d\\TH:i') }}" required></div>
        <div class="mb-0"><label class="form-label text-sm fw-medium">Notes</label><textarea name="notes" class="form-control" rows="2"></textarea></div>
    </div><div class="modal-footer border-top"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary-600">Save Payment</button></div></form>
</div></div></div>
@endsection
@section('scripts')
<script>
    const billsUrl = @json(url('get-student-bills'));
    const outstandingUrl = @json(url('get-student-outstanding-bills'));
    const paymentUrl = @json(route('record-bill-payment-process'));
    const csrfToken = @json(csrf_token());

    $('.view-bills-btn').on('click', function(){
        const id = $(this).data('id');
        $('#billBreakdownContent').html('<p class="text-secondary-light mb-0">Loading...</p>');
        $('#billBreakdownModal').modal('show');
        $.get(billsUrl + '/' + id, function(data){
            let rows = (data.bills || []).map(function(b){
                return '<tr><td>' + b.item_name + '</td><td>' + (b.term_name || '') + ' / ' + (b.year_name || '') + '</td><td>' + parseFloat(b.amount_due).toFixed(2) + '</td><td>' + parseFloat(b.amount_paid).toFixed(2) + '</td><td>' + parseFloat(b.balance).toFixed(2) + '</td><td>' + b.status + '</td></tr>';
            }).join('');
            $('#billBreakdownContent').html(
                '<p class="fw-semibold mb-8">' + data.student.full_name + ' (' + data.student.student_id + ')</p>' +
                '<table class="table bordered-table mb-0"><thead><tr><th>Item</th><th>Term/Year</th><th>Due</th><th>Paid</th><th>Balance</th><th>Status</th></tr></thead><tbody>' + rows + '</tbody></table>' +
                '<p class="mt-12 mb-0 fw-semibold">Outstanding: ' + parseFloat(data.summary.balance || 0).toFixed(2) + '</p>'
            );
        });
    });

    $('.record-payment-btn').on('click', function(){
        const id = $(this).data('id');
        $('#payment_student_id').val(id);
        $.get(outstandingUrl + '/' + id, function(data){
            $('#paymentStudentLabel').text(data.full_name + ' — Outstanding: ' + parseFloat(data.total_outstanding || 0).toFixed(2));
            $('#payment_amount').val(parseFloat(data.total_outstanding || 0).toFixed(2));
            $('#recordPaymentModal').modal('show');
        });
    });

    $('#recordPaymentForm').on('submit', function(e){
        e.preventDefault();
        $.ajax({
            url: paymentUrl,
            method: 'POST',
            data: $(this).serialize(),
            success: function(res){
                $('#recordPaymentModal').modal('hide');
                showAppToast('success', res.message || 'Payment recorded.');
                if(res.receipt_url){ window.open(res.receipt_url, '_blank'); }
                setTimeout(function(){ window.location.reload(); }, 800);
            },
            error: function(xhr){ showAppToast('error', xhr.responseJSON?.message || 'Unable to record payment.'); }
        });
    });
</script>
@endsection
