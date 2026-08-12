@extends('layouts.print')

@section('content')
@php
    $logoUrl = !empty($school->logo_path) ? asset($school->logo_path) : asset('assets/images/logo-icon.png');
@endphp

<div class="print-sheet">
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
            <div class="letterhead-meta">
                @if(!empty($school->address))<span><i class="ri-map-pin-line"></i> {{ $school->address }}</span>@endif
            </div>
        </div>
    </header>

    <div class="doc-head">
        <h1>Student Bill Statement</h1>
        <p>{{ $student->full_name }} ({{ $student->student_id }}) · Printed {{ $printedAt->format('d M Y g:i A') }}</p>
    </div>

    <div class="print-content">
        <div style="display:grid;grid-template-columns:repeat(5,1fr);gap:12px;margin-bottom:20px;">
            <div style="border:1px solid #e5e7eb;border-radius:10px;padding:12px;text-align:center;">
                <div style="font-size:11px;color:#6b7280;">Total Due</div>
                <div style="font-size:18px;font-weight:700;">₵{{ number_format($summary['total_due'], 2) }}</div>
            </div>
            <div style="border:1px solid #e5e7eb;border-radius:10px;padding:12px;text-align:center;">
                <div style="font-size:11px;color:#6b7280;">Total Paid</div>
                <div style="font-size:18px;font-weight:700;color:#15803d;">₵{{ number_format($summary['total_paid'], 2) }}</div>
            </div>
            <div style="border:1px solid #e5e7eb;border-radius:10px;padding:12px;text-align:center;">
                <div style="font-size:11px;color:#6b7280;">Outstanding</div>
                <div style="font-size:18px;font-weight:700;color:#dc2626;">₵{{ number_format($summary['balance'], 2) }}</div>
            </div>
            <div style="border:1px solid #bbf7d0;border-radius:10px;padding:12px;text-align:center;background:#f0fdf4;">
                <div style="font-size:11px;color:#15803d;">Remaining Credit</div>
                <div style="font-size:18px;font-weight:700;color:#15803d;">₵{{ number_format($summary['credit_balance'], 2) }}</div>
            </div>
            <div style="border:1px solid #c7d2fe;border-radius:10px;padding:12px;text-align:center;background:#eff6ff;">
                <div style="font-size:11px;color:#1d4ed8;">Net to Collect</div>
                <div style="font-size:18px;font-weight:700;color:#1d4ed8;">₵{{ number_format($summary['net_payable'], 2) }}</div>
            </div>
        </div>

        @if($summary['credit_balance'] > 0)
        <p style="margin:0 0 16px;padding:12px 14px;border-radius:10px;background:#f0fdf4;border:1px solid #bbf7d0;color:#166534;font-size:13px;">
            This student has <strong>₵{{ number_format($summary['credit_balance'], 2) }}</strong> remaining credit from overpayments.
            It can be applied on the record payment page to reduce the next payment by up to that amount.
        </p>
        @endif

        <p style="margin:0 0 12px;color:#6b7280;">
            Class: <strong>{{ $student->class_name }}</strong>
            · Category: <strong>{{ $student->schoolClass?->category?->name ?: '—' }}</strong>
            @if(!empty($filterLabels))
                · {{ implode(' · ', $filterLabels) }}
            @endif
        </p>

        <table class="info-table" style="width:100%;border-collapse:collapse;">
            <thead>
                <tr>
                    <th style="text-align:left;padding:8px;border-bottom:2px solid #e5e7eb;">Billing Item</th>
                    <th style="text-align:left;padding:8px;border-bottom:2px solid #e5e7eb;">Period</th>
                    <th style="text-align:right;padding:8px;border-bottom:2px solid #e5e7eb;">Due</th>
                    <th style="text-align:right;padding:8px;border-bottom:2px solid #e5e7eb;">Paid</th>
                    <th style="text-align:right;padding:8px;border-bottom:2px solid #e5e7eb;">Balance</th>
                    <th style="text-align:left;padding:8px;border-bottom:2px solid #e5e7eb;">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($bills as $bill)
                <tr>
                    <td style="padding:8px;border-bottom:1px solid #f3f4f6;">{{ $bill->billingItem?->name ?: 'Bill Item' }}</td>
                    <td style="padding:8px;border-bottom:1px solid #f3f4f6;">{{ collect([$bill->setup?->academicTerm?->name, $bill->setup?->academicYear?->name])->filter()->join(' · ') ?: '—' }}</td>
                    <td style="padding:8px;border-bottom:1px solid #f3f4f6;text-align:right;">{{ number_format($bill->amount_due, 2) }}</td>
                    <td style="padding:8px;border-bottom:1px solid #f3f4f6;text-align:right;">{{ number_format($bill->amount_paid, 2) }}</td>
                    <td style="padding:8px;border-bottom:1px solid #f3f4f6;text-align:right;">{{ number_format($bill->balance, 2) }}</td>
                    <td style="padding:8px;border-bottom:1px solid #f3f4f6;">{{ $bill->status }}</td>
                </tr>
                @empty
                <tr><td colspan="6" style="padding:24px;text-align:center;color:#6b7280;">No bill items found for this period.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
