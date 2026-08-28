@extends('layouts.super-admin')

@section('title', $school->name.' subscriptions')

@section('content')
<div class="sa-page-head">
  <h1>{{ $school->name }}</h1>
  <p>
    @if($school->code)
      <span class="sa-code">{{ $school->code }}</span>
    @endif
    <span class="sa-badge {{ $school->status }}" style="margin-left:8px;">{{ $school->status }}</span>
    <span style="margin-left:8px;">Payments for this school, and opening / vacation dates.</span>
  </p>
</div>

<div class="sa-panel">
  <div class="sa-panel-head">
    <h2><i class="ri-calendar-event-line"></i> Opening and vacation dates</h2>
    <a href="{{ route('super-admin.dashboard') }}" class="sa-btn sa-btn-danger-outline" style="border-color:rgba(124,92,255,0.35);color:#C4B5FD;">
      Back to dashboard
    </a>
  </div>
  <div class="sa-panel-body">
    @if($academicYears->isEmpty() || $academicTerms->isEmpty())
      <div class="sa-empty" style="padding:24px 0;">
        <i class="ri-calendar-line"></i>
        This school has no active academic year or term yet. Dates can be edited after those are created.
      </div>
    @else
      <form method="POST" action="{{ route('super-admin.schools.term-dates.update', $school) }}" id="sa-term-dates-form">
        @csrf
        <div class="sa-form-grid">
          <div class="sa-field">
            <label for="academic_year_id">Academic year</label>
            <select id="academic_year_id" name="academic_year_id" class="sa-input" required>
              @foreach($academicYears as $year)
                <option value="{{ $year->id }}" @selected((int) old('academic_year_id', $currentCalendar?->academic_year_id ?? $settings?->default_academic_year_id) === (int) $year->id)>
                  {{ $year->name }}
                </option>
              @endforeach
            </select>
            @error('academic_year_id')<span class="sa-field-error">{{ $message }}</span>@enderror
          </div>
          <div class="sa-field">
            <label for="academic_term_id">Academic term</label>
            <select id="academic_term_id" name="academic_term_id" class="sa-input" required>
              @foreach($academicTerms as $term)
                <option value="{{ $term->id }}" @selected((int) old('academic_term_id', $currentCalendar?->academic_term_id ?? $settings?->default_academic_term_id) === (int) $term->id)>
                  {{ $term->name }}
                </option>
              @endforeach
            </select>
            @error('academic_term_id')<span class="sa-field-error">{{ $message }}</span>@enderror
          </div>
          <div class="sa-field">
            <label for="opening_date">Opening / reopening date</label>
            <input type="date" id="opening_date" name="opening_date" class="sa-input" required
              value="{{ old('opening_date', $currentCalendar?->opening_date?->format('Y-m-d')) }}">
            @error('opening_date')<span class="sa-field-error">{{ $message }}</span>@enderror
          </div>
          <div class="sa-field">
            <label for="vacation_date">Vacation date</label>
            <input type="date" id="vacation_date" name="vacation_date" class="sa-input" required
              value="{{ old('vacation_date', $currentCalendar?->vacation_date?->format('Y-m-d')) }}">
            @error('vacation_date')<span class="sa-field-error">{{ $message }}</span>@enderror
          </div>
        </div>
        <div style="margin-top:20px;">
          <button type="submit" class="sa-btn sa-btn-success">
            <i class="ri-save-line"></i> Save term dates
          </button>
        </div>
      </form>
    @endif
  </div>
</div>

<div class="sa-panel">
  <div class="sa-panel-head">
    <h2><i class="ri-secure-payment-line"></i> Subscriptions made by this school</h2>
    <span style="font-size:12px;color:#83807A;">{{ $payments->count() }} payment(s)</span>
  </div>
  <div class="sa-table-wrap">
    <table class="sa-table">
      <thead>
        <tr>
          <th>Reference</th>
          <th>Plan</th>
          <th>Amount</th>
          <th>Payer</th>
          <th>Status</th>
          <th>Paid</th>
          <th>Activated</th>
        </tr>
      </thead>
      <tbody>
        @forelse($payments as $payment)
          <tr>
            <td><span class="sa-code">{{ $payment->paystack_reference }}</span></td>
            <td>{{ $payment->subscription?->name ?: '—' }}</td>
            <td>{{ \App\Support\Money::ghs($payment->amount) }}</td>
            <td>
              <div style="color:#F5F3EE;">{{ $payment->payer_full_name }}</div>
              <div style="font-size:12px;color:#83807A;margin-top:4px;">{{ $payment->payer_phone }}</div>
              <div style="font-size:12px;color:#83807A;">{{ $payment->payer_email }}</div>
            </td>
            <td><span class="sa-badge {{ $payment->status }}">{{ $payment->status }}</span></td>
            <td>{{ $payment->paid_at?->format('d M Y H:i') ?: '—' }}</td>
            <td>{{ $payment->activated_at?->format('d M Y H:i') ?: '—' }}</td>
          </tr>
        @empty
          <tr>
            <td colspan="7">
              <div class="sa-empty">
                <i class="ri-bank-card-line"></i>
                This school has not made any subscription payments yet.
              </div>
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>
@endsection

@push('scripts')
<script>
  (function () {
    const calendars = @json($termCalendars);
    const yearSelect = document.getElementById('academic_year_id');
    const termSelect = document.getElementById('academic_term_id');
    const opening = document.getElementById('opening_date');
    const vacation = document.getElementById('vacation_date');

    if (!yearSelect || !termSelect || !opening || !vacation) {
      return;
    }

    function fillDates() {
      const key = yearSelect.value + ':' + termSelect.value;
      const dates = calendars[key];
      if (!dates) {
        return;
      }
      if (dates.opening_date) {
        opening.value = dates.opening_date;
      }
      if (dates.vacation_date) {
        vacation.value = dates.vacation_date;
      }
    }

    yearSelect.addEventListener('change', fillDates);
    termSelect.addEventListener('change', fillDates);

    if (!opening.value && !vacation.value) {
      fillDates();
    }
  })();
</script>
@endpush
