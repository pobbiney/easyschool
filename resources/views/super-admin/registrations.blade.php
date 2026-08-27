@extends('layouts.super-admin')

@section('title', 'Pending Registrations')

@push('styles')
<style>
  .sa-reg-card {
    border-radius: 16px;
    background: rgba(17, 20, 19, 0.82);
    border: 1px solid rgba(255,255,255,0.07);
    padding: 24px;
    margin-bottom: 16px;
    transition: border-color 0.15s;
  }

  .sa-reg-card:hover { border-color: rgba(124,92,255,0.25); }

  .sa-reg-head {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 20px;
    flex-wrap: wrap;
  }

  .sa-reg-head h2 {
    font-family: 'Sora', sans-serif;
    font-size: 18px;
    font-weight: 700;
    color: #F5F3EE;
    margin-bottom: 8px;
  }

  .sa-reg-meta {
    font-size: 13px;
    color: #83807A;
    line-height: 1.7;
  }

  .sa-reg-meta i { color: #9F8CFF; margin-right: 4px; vertical-align: -2px; }

  .sa-reg-actions {
    display: flex;
    flex-direction: column;
    gap: 8px;
    min-width: 140px;
  }

  .sa-reg-actions .sa-btn { justify-content: center; width: 100%; }

  .sa-reject-box {
    margin-top: 20px;
    padding-top: 20px;
    border-top: 1px solid rgba(255,255,255,0.06);
    display: none;
  }

  .sa-reject-box.open { display: block; }

  .sa-reject-box textarea {
    width: 100%;
    background: rgba(255,255,255,0.03);
    border: 1px solid rgba(255,255,255,0.09);
    border-radius: 10px;
    padding: 12px 14px;
    color: #F5F3EE;
    font-family: 'Inter', sans-serif;
    font-size: 14px;
    resize: vertical;
    min-height: 80px;
    margin-bottom: 12px;
  }

  .sa-reject-box textarea:focus {
    outline: none;
    border-color: rgba(244,63,94,0.5);
    background: rgba(244,63,94,0.05);
  }

  .sa-reject-box textarea::placeholder { color: #706D66; }
</style>
@endpush

@section('content')
<div class="sa-page-head">
  <h1>Pending registrations</h1>
  <p>Review new school applications, approve to generate a school code, or reject with a reason.</p>
</div>

@forelse($schools as $school)
  <div class="sa-reg-card" id="reg-{{ $school->id }}">
    <div class="sa-reg-head">
      <div>
        <h2>{{ $school->name }}</h2>
        <div class="sa-reg-meta">
          @if($school->email)<div><i class="ri-mail-line"></i> {{ $school->email }}</div>@endif
          @if($school->phone)<div><i class="ri-phone-line"></i> {{ $school->phone }}</div>@endif
          @if($school->address)<div><i class="ri-map-pin-line"></i> {{ $school->address }}</div>@endif
          <div style="margin-top:10px;padding-top:10px;border-top:1px solid rgba(255,255,255,0.06);">
            <strong style="color:#C9C6BE;">Administrator</strong><br>
            {{ $school->admin_name }} · {{ $school->admin_email }}
            @if($school->admin_phone) · {{ $school->admin_phone }}@endif
          </div>
          <div style="margin-top:6px;font-size:12px;color:#706D66;">
            <i class="ri-time-line"></i> Submitted {{ $school->created_at?->diffForHumans() }}
          </div>
        </div>
      </div>
      <div class="sa-reg-actions">
        <form method="POST" action="{{ route('super-admin.registrations.approve', $school) }}">
          @csrf
          <button type="submit" class="sa-btn sa-btn-success">
            <i class="ri-check-double-line"></i> Approve
          </button>
        </form>
        <button type="button" class="sa-btn sa-btn-danger-outline" onclick="toggleReject({{ $school->id }})">
          <i class="ri-close-circle-line"></i> Reject
        </button>
      </div>
    </div>
    <div class="sa-reject-box" id="reject-box-{{ $school->id }}">
      <form method="POST" action="{{ route('super-admin.registrations.reject', $school) }}">
        @csrf
        <textarea name="rejection_reason" placeholder="Reason for rejection (shown internally)" required></textarea>
        <button type="submit" class="sa-btn sa-btn-danger">
          <i class="ri-forbid-line"></i> Confirm rejection
        </button>
      </form>
    </div>
  </div>
@empty
  <div class="sa-panel">
    <div class="sa-empty">
      <i class="ri-inbox-line"></i>
      No pending registrations at the moment.
      <div style="margin-top:16px;">
        <a href="{{ route('super-admin.dashboard') }}" class="sa-btn sa-btn-primary">
          <i class="ri-arrow-left-line"></i> Back to dashboard
        </a>
      </div>
    </div>
  </div>
@endforelse
@endsection

@push('scripts')
<script>
  function toggleReject(id) {
    const box = document.getElementById('reject-box-' + id);
    box.classList.toggle('open');
  }
</script>
@endpush
