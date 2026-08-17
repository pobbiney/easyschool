<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Receipt - {{ $payment->receipt_no }}</title>
    <style>
        body { font-family: Arial, sans-serif; color: #111827; margin: 0; padding: 24px; background: #f3f4f6; }
        .receipt { max-width: 760px; margin: 0 auto; background: #fff; border: 1px solid #e5e7eb; border-radius: 12px; padding: 32px; }
        .header { display: flex; justify-content: space-between; gap: 16px; border-bottom: 2px solid #25A194; padding-bottom: 16px; margin-bottom: 24px; }
        .school-name { font-size: 22px; font-weight: 700; color: #25A194; }
        .meta { font-size: 13px; color: #6b7280; line-height: 1.6; }
        .title { font-size: 18px; font-weight: 700; margin-bottom: 16px; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th, td { border: 1px solid #e5e7eb; padding: 10px 12px; text-align: left; font-size: 14px; }
        th { background: #f9fafb; }
        .total-box { margin-top: 20px; padding: 16px; background: rgba(37,161,148,.08); border-radius: 8px; font-size: 16px; font-weight: 700; }
        .actions { margin-top: 24px; display: flex; gap: 12px; }
        .btn { border: none; border-radius: 8px; padding: 10px 18px; cursor: pointer; font-weight: 600; }
        .btn-primary { background: #25A194; color: #fff; }
        .btn-secondary { background: #e5e7eb; color: #374151; }
        @media print { body { background: #fff; padding: 0; } .actions { display: none; } .receipt { border: none; } }
    </style>
</head>
<body>
    <div class="receipt">
        <div class="header">
            <div>
                <div class="school-name">{{ $school->school_name ?? 'School' }}</div>
                <div class="meta">{{ $school->address ?? '' }}</div>
            </div>
            <div class="meta" style="text-align:right;">
                <div><strong>Receipt No:</strong> {{ $payment->receipt_no }}</div>
                <div><strong>Date:</strong> {{ $payment->paid_at->format('M j, Y g:i A') }}</div>
            </div>
        </div>

        <div class="title">Payment Receipt</div>
        <div class="meta">
            <div><strong>Student:</strong> {{ $payment->student->full_name }} ({{ $payment->student->student_id }})</div>
            <div><strong>Class:</strong> {{ $payment->student->class_name }}</div>
            <div><strong>Category:</strong> {{ $payment->student->schoolClass?->category?->name ?: '—' }}</div>
            <div><strong>Method:</strong> {{ $payment->payment_method }} @if($payment->reference) | <strong>Ref:</strong> {{ $payment->reference }} @endif</div>
        </div>

        <table>
            <thead><tr><th>Billing Item</th><th>Amount Applied</th></tr></thead>
            <tbody>
                @foreach($payment->allocations as $allocation)
                <tr>
                    <td>{{ $allocation->studentBill?->billingItem?->name ?: 'Bill Item' }}</td>
                    <td>{{ number_format($allocation->amount, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="total-box">Total Paid: {{ number_format($payment->amount, 2) }}</div>
        @if($payment->notes)<p class="meta" style="margin-top:16px;"><strong>Notes:</strong> {{ $payment->notes }}</p>@endif

        <div class="actions">
            <button class="btn btn-primary" onclick="window.print()">Print Receipt</button>
            <a href="{{ route('student-bills') }}" class="btn btn-secondary" style="text-decoration:none;display:inline-flex;align-items:center;">Back to Student Bills</a>
        </div>
    </div>
</body>
</html>
