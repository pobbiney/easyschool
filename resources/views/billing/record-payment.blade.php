@php
    $pageName = 'bill-management';
    $subpageName = 'student-bills';
    $initials = strtoupper(substr($student->firstname, 0, 1) . substr($student->surname, 0, 1));
    $photoUrl = $student->picture ? asset($student->picture) : null;
@endphp
@extends('layouts.app')

@section('css')
<style>
    .fc-page { --fc-teal: #25A194; --fc-indigo: #6366f1; --fc-dark: #0f172a; min-height: calc(100vh - 120px); }

    .fc-topbar {
        display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 16px;
        margin-bottom: 20px;
    }
    .fc-back {
        display: inline-flex; align-items: center; gap: 8px; padding: 10px 16px;
        border-radius: 12px; border: 1px solid #e5e7eb; background: #fff; color: #374151;
        font-size: 13px; font-weight: 600; text-decoration: none; transition: all .15s;
        box-shadow: 0 1px 3px rgba(15,23,42,.04);
    }
    .fc-back:hover { border-color: var(--fc-teal); color: var(--fc-teal); background: rgba(37,161,148,.04); }
    .fc-clock {
        display: inline-flex; align-items: center; gap: 8px; padding: 10px 16px;
        border-radius: 12px; background: var(--fc-dark); color: #fff; font-size: 13px; font-weight: 600;
    }
    .fc-clock i { color: #34d399; }

    .fc-profile {
        position: relative;
        border-radius: 20px;
        margin-bottom: 20px;
        background: #fff;
        border: 1px solid #e5e7eb;
        overflow: hidden;
        box-shadow: 0 8px 30px rgba(15,23,42,.06);
    }
    .fc-profile-accent {
        height: 5px;
        background: linear-gradient(90deg, var(--fc-teal), var(--fc-indigo));
    }
    .fc-profile-inner {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 24px;
        padding: 24px 28px;
    }
    .fc-profile-student {
        display: flex;
        align-items: center;
        gap: 20px;
        min-width: 0;
        flex: 1;
    }
    .fc-profile-photo {
        width: 96px;
        height: 96px;
        border-radius: 18px;
        flex-shrink: 0;
        overflow: hidden;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 28px;
        font-weight: 800;
        color: #fff;
        background: linear-gradient(135deg, var(--fc-teal), #17897e);
        border: 4px solid rgba(37,161,148,.12);
        box-shadow: 0 8px 24px rgba(37,161,148,.22);
    }
    .fc-profile-photo img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .fc-profile-meta { min-width: 0; }
    .fc-profile-eyebrow {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .06em;
        color: var(--fc-teal);
        margin-bottom: 8px;
    }
    .fc-profile-name {
        font-size: 28px;
        font-weight: 800;
        color: #111827;
        margin: 0 0 6px;
        letter-spacing: -.02em;
        line-height: 1.15;
    }
    .fc-profile-id {
        font-size: 14px;
        color: #6b7280;
        margin: 0 0 14px;
        font-weight: 500;
    }
    .fc-tags { display: flex; flex-wrap: wrap; gap: 8px; }
    .fc-tag {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 7px 12px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 600;
        color: #374151;
        background: #f9fafb;
        border: 1px solid #e5e7eb;
    }
    .fc-tag i { color: var(--fc-teal); font-size: 14px; }

    .fc-profile-total {
        flex-shrink: 0;
        min-width: 220px;
        padding: 20px 22px;
        border-radius: 16px;
        background: linear-gradient(135deg, #fff7ed, #fffbeb);
        border: 1px solid #fde68a;
        text-align: center;
        box-shadow: 0 4px 16px rgba(245,158,11,.08);
    }
    .fc-profile-total-lbl {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .07em;
        color: #b45309;
        margin-bottom: 8px;
    }
    .fc-profile-total-val {
        font-size: 34px;
        font-weight: 800;
        color: #dc2626;
        letter-spacing: -.03em;
        line-height: 1;
    }
    .fc-profile-total-sub {
        font-size: 12px;
        color: #92400e;
        margin-top: 8px;
        font-weight: 600;
    }

    @media (max-width: 768px) {
        .fc-profile-inner { padding: 20px; }
        .fc-profile-student { flex-direction: column; align-items: flex-start; }
        .fc-profile-photo { width: 84px; height: 84px; }
        .fc-profile-name { font-size: 24px; }
        .fc-profile-total { width: 100%; min-width: 0; }
    }

    .fc-grid { display: grid; grid-template-columns: minmax(0, 1.1fr) minmax(340px, 420px); gap: 20px; align-items: start; }
    @media (max-width: 991px) { .fc-grid { grid-template-columns: 1fr; } }

    .fc-card {
        background: #fff; border-radius: 18px; border: 1px solid #e5e7eb;
        box-shadow: 0 4px 20px rgba(15,23,42,.05); overflow: hidden;
    }
    .fc-card-head {
        display: flex; align-items: center; justify-content: space-between; gap: 12px;
        padding: 18px 22px; border-bottom: 1px solid #f3f4f6;
        background: linear-gradient(180deg, #fff, #fafafa);
    }
    .fc-card-head h6 {
        font-size: 13px; font-weight: 800; text-transform: uppercase; letter-spacing: .06em;
        color: #6b7280; margin: 0; display: flex; align-items: center; gap: 8px;
    }
    .fc-card-head h6 i { color: var(--fc-teal); font-size: 16px; }
    .fc-count {
        font-size: 11px; font-weight: 700; padding: 4px 10px; border-radius: 999px;
        background: rgba(37,161,148,.1); color: var(--fc-teal);
    }

    .fc-actions { display: flex; gap: 8px; }
    .fc-mini-btn {
        padding: 7px 12px; border-radius: 10px; border: 1px solid #e5e7eb; background: #fff;
        font-size: 11px; font-weight: 700; color: #374151; cursor: pointer; transition: all .12s;
    }
    .fc-mini-btn:hover { border-color: var(--fc-teal); color: var(--fc-teal); background: rgba(37,161,148,.04); }

    .fc-lines { padding: 14px 18px 18px; max-height: 460px; overflow-y: auto; }
    .fc-line {
        display: flex; align-items: center; gap: 14px; padding: 16px 18px;
        border-radius: 16px; margin-bottom: 10px; cursor: pointer;
        border: 1px solid #f3f4f6; background: linear-gradient(135deg, #fafafa, #fff);
        transition: all .15s;
    }
    .fc-line:hover { border-color: rgba(37,161,148,.25); box-shadow: 0 4px 16px rgba(15,23,42,.06); transform: translateY(-1px); }
    .fc-line.is-selected {
        border-color: rgba(37,161,148,.4); background: linear-gradient(135deg, rgba(37,161,148,.06), rgba(99,102,241,.04));
        box-shadow: 0 0 0 1px rgba(37,161,148,.15), 0 4px 16px rgba(37,161,148,.08);
    }
    .fc-check {
        width: 24px; height: 24px; border-radius: 8px; flex-shrink: 0;
        border: 2px solid #d1d5db; display: inline-flex; align-items: center; justify-content: center;
        font-size: 14px; color: transparent; transition: all .12s;
    }
    .fc-line.is-selected .fc-check { background: linear-gradient(135deg, var(--fc-teal), #1d8a80); border-color: var(--fc-teal); color: #fff; }
    .fc-line-icon {
        width: 44px; height: 44px; border-radius: 12px; flex-shrink: 0;
        display: inline-flex; align-items: center; justify-content: center; font-size: 20px;
        background: rgba(99,102,241,.1); color: var(--fc-indigo);
    }
    .fc-line.is-selected .fc-line-icon { background: rgba(37,161,148,.12); color: var(--fc-teal); }
    .fc-line-info { flex: 1; min-width: 0; }
    .fc-line-name { font-size: 14px; font-weight: 700; color: #111827; margin-bottom: 3px; }
    .fc-line-meta { font-size: 11px; color: #9ca3af; }
    .fc-required {
        font-size: 9px; font-weight: 800; text-transform: uppercase; letter-spacing: .05em;
        padding: 2px 7px; border-radius: 5px; margin-left: 8px;
        background: rgba(245,158,11,.12); color: #b45309; vertical-align: middle;
    }
    .fc-line-amt { text-align: right; flex-shrink: 0; }
    .fc-line-balance { font-size: 17px; font-weight: 800; color: #111827; }
    .fc-line-due { font-size: 10px; color: #9ca3af; margin-top: 2px; }

    .fc-cart-foot {
        padding: 18px 22px; border-top: 1px solid #e5e7eb;
        background: linear-gradient(180deg, #fafafa, #fff);
    }
    .fc-cart-row { display: flex; justify-content: space-between; font-size: 13px; color: #6b7280; margin-bottom: 8px; }
    .fc-cart-row.total {
        font-size: 15px; font-weight: 800; color: #111827; margin: 0; padding-top: 12px;
        border-top: 1px dashed #e5e7eb;
    }
    .fc-cart-row.total span:last-child { font-size: 22px; color: #dc2626; }

    .fc-terminal { position: sticky; top: 20px; }
    .fc-display {
        margin: 18px 18px 0; padding: 22px 24px; border-radius: 18px;
        background: linear-gradient(145deg, #0f172a, #1e293b);
        color: #fff; position: relative; overflow: hidden;
        box-shadow: inset 0 1px 0 rgba(255,255,255,.08), 0 8px 24px rgba(15,23,42,.2);
    }
    .fc-display::before {
        content: ''; position: absolute; top: 0; right: 0; width: 120px; height: 120px;
        background: radial-gradient(circle, rgba(52,211,153,.15), transparent 70%);
    }
    .fc-display-lbl { font-size: 10px; text-transform: uppercase; letter-spacing: .08em; opacity: .55; margin-bottom: 6px; }
    .fc-display-amt { font-size: 38px; font-weight: 800; letter-spacing: -.03em; line-height: 1; color: #34d399; position: relative; }
    .fc-display-row {
        display: flex; justify-content: space-between; margin-top: 14px; padding-top: 14px;
        border-top: 1px solid rgba(255,255,255,.1); font-size: 12px; opacity: .75; position: relative;
    }
    .fc-display-change { color: #fbbf24; font-weight: 800; font-size: 14px; }

    .fc-panel-body { padding: 18px; }
    .fc-quick { display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px; margin-bottom: 16px; }
    .fc-quick-btn {
        padding: 10px 8px; border-radius: 11px; border: 1px solid #e5e7eb; background: #fff;
        font-size: 11px; font-weight: 800; color: #374151; cursor: pointer; transition: all .12s;
    }
    .fc-quick-btn:hover { border-color: var(--fc-teal); color: var(--fc-teal); background: rgba(37,161,148,.05); }

    .fc-field { margin-bottom: 14px; }
    .fc-field label {
        font-size: 10px; font-weight: 800; text-transform: uppercase; letter-spacing: .06em;
        color: #6b7280; margin-bottom: 7px; display: block;
    }
    .fc-field .form-control {
        border-radius: 12px; min-height: 46px; font-size: 14px; border-color: #e5e7eb;
    }
    .fc-field .form-control:focus { border-color: var(--fc-teal); box-shadow: 0 0 0 3px rgba(37,161,148,.12); }
    .fc-amt-wrap { position: relative; }
    .fc-amt-prefix {
        position: absolute; left: 16px; top: 50%; transform: translateY(-50%);
        font-size: 18px; font-weight: 800; color: var(--fc-teal); pointer-events: none;
    }
    .fc-amt-wrap .form-control { padding-left: 40px; font-size: 20px; font-weight: 800; min-height: 54px; }

    .fc-methods { display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px; margin-bottom: 14px; }
    .fc-method {
        display: flex; flex-direction: column; align-items: center; gap: 6px;
        padding: 16px 10px; border-radius: 14px; border: 2px solid #e5e7eb; background: #fff;
        cursor: pointer; transition: all .12s;
    }
    .fc-method i { font-size: 26px; color: #9ca3af; transition: color .12s; }
    .fc-method span { font-size: 11px; font-weight: 800; color: #374151; }
    .fc-method.is-active {
        border-color: var(--fc-teal); background: linear-gradient(135deg, rgba(37,161,148,.08), rgba(99,102,241,.04));
        box-shadow: 0 0 0 1px rgba(37,161,148,.15);
    }
    .fc-method.is-active i { color: var(--fc-teal); }
    .fc-method.is-active span { color: var(--fc-teal); }

    .fc-ref-field { display: none; }
    .fc-ref-field.is-visible { display: block; }

    .fc-submit-wrap { padding: 0 18px 20px; }
    .fc-submit {
        width: 100%; min-height: 56px; border: none; border-radius: 14px;
        background: linear-gradient(135deg, var(--fc-teal), #1d8a80 50%, var(--fc-indigo));
        color: #fff; font-size: 16px; font-weight: 800; letter-spacing: .02em;
        display: inline-flex; align-items: center; justify-content: center; gap: 10px;
        box-shadow: 0 8px 24px rgba(37,161,148,.35); transition: transform .12s, box-shadow .12s;
    }
    .fc-submit:hover:not(:disabled) { transform: translateY(-2px); box-shadow: 0 12px 28px rgba(37,161,148,.4); color: #fff; }
    .fc-submit:disabled { opacity: .5; cursor: not-allowed; transform: none; box-shadow: none; }

    .fc-hint {
        text-align: center; font-size: 11px; color: #9ca3af; margin-top: 10px;
    }
</style>
@endsection

@section('content')
<div class="dashboard-main-body fc-page">
    <div class="fc-topbar">
        <a href="{{ route('student-bills') }}" class="fc-back"><i class="ri-arrow-left-line"></i> Back to Student Bills</a>
        <div class="fc-clock"><i class="ri-time-line"></i> <span id="fc_clock">{{ now()->format('D, M j Y · g:i A') }}</span></div>
    </div>

    <div class="fc-profile">
        <div class="fc-profile-accent"></div>
        <div class="fc-profile-inner">
            <div class="fc-profile-student">
                <div class="fc-profile-photo">
                    @if($photoUrl)
                        <img src="{{ $photoUrl }}" alt="{{ $student->full_name }}">
                    @else
                        {{ $initials }}
                    @endif
                </div>
                <div class="fc-profile-meta">
                    <div class="fc-profile-eyebrow"><i class="ri-store-2-line"></i> Fee Cashier</div>
                    <h1 class="fc-profile-name">{{ $student->full_name }}</h1>
                    <p class="fc-profile-id">Student ID · {{ $student->student_id }}</p>
                    <div class="fc-tags">
                        @if($student->class_name)
                            <span class="fc-tag"><i class="ri-book-open-line"></i> {{ $student->class_name }}</span>
                        @endif
                        @if($student->schoolClass?->category?->name)
                            <span class="fc-tag"><i class="ri-stack-line"></i> {{ $student->schoolClass->category->name }}</span>
                        @endif
                        @if($student->gender)
                            <span class="fc-tag"><i class="ri-user-line"></i> {{ $student->gender }}</span>
                        @endif
                    </div>
                </div>
            </div>
            <div class="fc-profile-total">
                <div class="fc-profile-total-lbl">Total outstanding</div>
                <div class="fc-profile-total-val">₵{{ number_format($totalOutstanding, 2) }}</div>
                <div class="fc-profile-total-sub">{{ $bills->count() }} bill item{{ $bills->count() === 1 ? '' : 's' }} due</div>
            </div>
        </div>
    </div>

    <form id="recordPaymentForm">
        @csrf
        <input type="hidden" name="student_id" value="{{ $student->id }}">
        <input type="hidden" name="payment_method" id="fc_payment_method" value="Cash">

        <div class="fc-grid">
            <div class="fc-card">
                <div class="fc-card-head">
                    <h6><i class="ri-shopping-cart-2-line"></i> Outstanding items <span class="fc-count" id="fc_selected_count">{{ $bills->count() }} selected</span></h6>
                    <div class="fc-actions">
                        <button type="button" class="fc-mini-btn" id="fc_select_all">Select all</button>
                        <button type="button" class="fc-mini-btn" id="fc_clear_all">Clear</button>
                    </div>
                </div>
                <div class="fc-lines" id="fc_bill_lines">
                    @foreach($bills as $bill)
                        @php $period = collect([$bill['term_name'], $bill['year_name']])->filter()->join(' · '); @endphp
                        <div class="fc-line is-selected" data-bill-id="{{ $bill['id'] }}" data-balance="{{ $bill['balance'] }}">
                            <span class="fc-check"><i class="ri-check-line"></i></span>
                            <div class="fc-line-icon"><i class="ri-bill-line"></i></div>
                            <div class="fc-line-info">
                                <div class="fc-line-name">
                                    {{ $bill['item_name'] }}
                                    @if($bill['is_compulsory'])<span class="fc-required">Required</span>@endif
                                </div>
                                <div class="fc-line-meta">{{ $period ?: 'Outstanding balance' }} · Due ₵{{ number_format($bill['amount_due'], 2) }} · Paid ₵{{ number_format($bill['amount_paid'], 2) }}</div>
                            </div>
                            <div class="fc-line-amt">
                                <div class="fc-line-balance">₵{{ number_format($bill['balance'], 2) }}</div>
                                <div class="fc-line-due">balance</div>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="fc-cart-foot">
                    <div class="fc-cart-row"><span>Selected total</span><span id="fc_cart_subtotal">₵{{ number_format($totalOutstanding, 2) }}</span></div>
                    <div class="fc-cart-row total"><span>Amount to collect</span><span id="fc_cart_total">₵{{ number_format($totalOutstanding, 2) }}</span></div>
                </div>
            </div>

            <div class="fc-terminal">
                <div class="fc-card">
                    <div class="fc-display">
                        <div class="fc-display-lbl">Payment amount</div>
                        <div class="fc-display-amt" id="fc_display_amount">₵{{ number_format($totalOutstanding, 2) }}</div>
                        <div class="fc-display-row">
                            <span>Change due</span>
                            <span class="fc-display-change" id="fc_display_change">—</span>
                        </div>
                    </div>

                    <div class="fc-panel-body">
                        <div class="fc-quick">
                            <button type="button" class="fc-quick-btn" data-fc-quick="full">Pay full</button>
                            <button type="button" class="fc-quick-btn" data-fc-quick="half">Half</button>
                            <button type="button" class="fc-quick-btn" data-fc-quick="clear">Clear</button>
                        </div>

                        <div class="fc-field">
                            <label>Amount to pay</label>
                            <div class="fc-amt-wrap">
                                <span class="fc-amt-prefix">₵</span>
                                <input type="number" min="0.01" step="0.01" name="amount" id="fc_payment_amount" class="form-control" value="{{ number_format($totalOutstanding, 2, '.', '') }}" required>
                            </div>
                        </div>

                        <div class="fc-field" id="fc_tendered_field">
                            <label>Amount received (cash)</label>
                            <div class="fc-amt-wrap">
                                <span class="fc-amt-prefix">₵</span>
                                <input type="number" min="0" step="0.01" id="fc_amount_tendered" class="form-control" value="{{ number_format($totalOutstanding, 2, '.', '') }}">
                            </div>
                        </div>

                        <div class="fc-field">
                            <label>Payment method</label>
                            <div class="fc-methods">
                                <div class="fc-method is-active" data-method="Cash"><i class="ri-money-dollar-box-line"></i><span>Cash</span></div>
                                <div class="fc-method" data-method="Mobile Money"><i class="ri-smartphone-line"></i><span>MoMo</span></div>
                                <div class="fc-method" data-method="Bank"><i class="ri-bank-line"></i><span>Bank</span></div>
                                <div class="fc-method" data-method="Cheque"><i class="ri-file-paper-2-line"></i><span>Cheque</span></div>
                            </div>
                        </div>

                        <div class="fc-field fc-ref-field" id="fc_ref_field">
                            <label>Reference / transaction ID</label>
                            <input type="text" name="reference" class="form-control" placeholder="Enter reference number">
                        </div>

                        <div class="fc-field">
                            <label>Payment date</label>
                            <input type="datetime-local" name="paid_at" class="form-control" value="{{ now()->format('Y-m-d\\TH:i') }}" required>
                        </div>

                        <div class="fc-field mb-0">
                            <label>Notes (optional)</label>
                            <input type="text" name="notes" class="form-control" placeholder="Any note for this payment">
                        </div>
                    </div>

                    <div class="fc-submit-wrap">
                        <button type="submit" class="fc-submit" id="fc_submit_btn">
                            <i class="ri-printer-line"></i> Complete payment &amp; print receipt
                        </button>
                        <p class="fc-hint">Receipt opens automatically after successful payment</p>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@section('scripts')
<script>
    const paymentUrl = @json(route('record-bill-payment-process'));
    const studentBillsUrl = @json(route('student-bills'));
    const billData = @json($bills->values());
    let selectedIds = new Set(billData.map(function(b){ return b.id; }));

    function formatMoney(value) {
        return parseFloat(value || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function selectedBills() {
        return billData.filter(function(b){ return selectedIds.has(b.id); });
    }

    function selectedTotal() {
        return selectedBills().reduce(function(sum, b){ return sum + parseFloat(b.balance || 0); }, 0);
    }

    function buildAllocations(paymentAmount) {
        let remaining = parseFloat(paymentAmount || 0);
        const allocations = [];
        selectedBills().forEach(function(bill) {
            if (remaining <= 0) return;
            const amount = Math.min(parseFloat(bill.balance || 0), remaining);
            if (amount > 0) {
                allocations.push({ student_bill_id: bill.id, amount: amount.toFixed(2) });
                remaining -= amount;
            }
        });
        return allocations;
    }

    function updateDisplay() {
        const total = selectedTotal();
        const payAmount = parseFloat($('#fc_payment_amount').val() || 0);
        const tendered = parseFloat($('#fc_amount_tendered').val() || 0);
        const method = $('#fc_payment_method').val();
        const change = method === 'Cash' && tendered > payAmount ? tendered - payAmount : 0;

        $('#fc_display_amount').text('₵' + formatMoney(payAmount));
        $('#fc_selected_count').text(selectedBills().length + ' selected');
        $('#fc_cart_subtotal').text('₵' + formatMoney(total));
        $('#fc_cart_total').text('₵' + formatMoney(total));
        $('#fc_display_change').text(change > 0 ? '₵' + formatMoney(change) : '—');
        $('#fc_submit_btn').prop('disabled', payAmount <= 0 || selectedBills().length === 0);

        if (method === 'Cash') {
            $('#fc_ref_field').removeClass('is-visible');
            $('#fc_tendered_field').show();
        } else {
            $('#fc_ref_field').addClass('is-visible');
            $('#fc_tendered_field').hide();
        }
    }

    $('.fc-line').on('click', function() {
        const id = parseInt($(this).data('bill-id'), 10);
        if (selectedIds.has(id)) {
            selectedIds.delete(id);
            $(this).removeClass('is-selected');
        } else {
            selectedIds.add(id);
            $(this).addClass('is-selected');
        }
        const total = selectedTotal();
        $('#fc_payment_amount').val(total > 0 ? total.toFixed(2) : '');
        $('#fc_amount_tendered').val(total > 0 ? total.toFixed(2) : '');
        updateDisplay();
    });

    $('#fc_select_all').on('click', function() {
        selectedIds = new Set(billData.map(function(b){ return b.id; }));
        $('.fc-line').addClass('is-selected');
        const total = selectedTotal();
        $('#fc_payment_amount').val(total.toFixed(2));
        $('#fc_amount_tendered').val(total.toFixed(2));
        updateDisplay();
    });

    $('#fc_clear_all').on('click', function() {
        selectedIds.clear();
        $('.fc-line').removeClass('is-selected');
        $('#fc_payment_amount').val('');
        $('#fc_amount_tendered').val('');
        updateDisplay();
    });

    $('.fc-method').on('click', function() {
        $('.fc-method').removeClass('is-active');
        $(this).addClass('is-active');
        $('#fc_payment_method').val($(this).data('method'));
        updateDisplay();
    });

    $('[data-fc-quick]').on('click', function() {
        const action = $(this).data('fc-quick');
        const total = selectedTotal();
        if (action === 'full') {
            $('#fc_payment_amount').val(total.toFixed(2));
            $('#fc_amount_tendered').val(total.toFixed(2));
        } else if (action === 'half') {
            const half = (total / 2).toFixed(2);
            $('#fc_payment_amount').val(half);
            $('#fc_amount_tendered').val(half);
        } else {
            $('#fc_payment_amount').val('');
            $('#fc_amount_tendered').val('');
        }
        updateDisplay();
    });

    $('#fc_payment_amount, #fc_amount_tendered').on('input', updateDisplay);

    $('#recordPaymentForm').on('submit', function(e) {
        e.preventDefault();
        const payAmount = parseFloat($('#fc_payment_amount').val() || 0);
        const allocations = buildAllocations(payAmount);

        if (!allocations.length) {
            showAppToast('error', 'Select at least one bill to pay.');
            return;
        }

        const total = selectedTotal();
        if (payAmount > total + 0.009) {
            showAppToast('error', 'Payment amount cannot exceed selected bills total (₵' + formatMoney(total) + ').');
            return;
        }

        const payload = $(this).serializeArray();
        allocations.forEach(function(a, i) {
            payload.push({ name: 'allocations[' + i + '][student_bill_id]', value: a.student_bill_id });
            payload.push({ name: 'allocations[' + i + '][amount]', value: a.amount });
        });

        const $btn = $('#fc_submit_btn');
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Processing...');

        $.ajax({
            url: paymentUrl,
            method: 'POST',
            data: $.param(payload),
            success: function(res) {
                showAppToast('success', res.message || 'Payment recorded.');
                if (res.receipt_url) { window.open(res.receipt_url, '_blank'); }
                setTimeout(function(){ window.location.href = studentBillsUrl; }, 900);
            },
            error: function(xhr) {
                showAppToast('error', xhr.responseJSON?.message || 'Unable to record payment.');
                $btn.prop('disabled', false).html('<i class="ri-printer-line"></i> Complete payment & print receipt');
            }
        });
    });

    setInterval(function() {
        const now = new Date();
        $('#fc_clock').text(now.toLocaleString(undefined, { weekday: 'short', month: 'short', day: 'numeric', year: 'numeric', hour: 'numeric', minute: '2-digit' }));
    }, 30000);

    updateDisplay();
</script>
@endsection
