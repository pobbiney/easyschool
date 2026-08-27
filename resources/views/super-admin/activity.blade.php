@extends('layouts.super-admin')

@section('title', 'Activity Log')

@push('styles')
<style>
  .sa-pagination {
    padding: 16px 24px;
    border-top: 1px solid rgba(255,255,255,0.06);
    display: flex;
    justify-content: center;
  }

  .sa-pagination nav { display: flex; gap: 6px; flex-wrap: wrap; justify-content: center; }

  .sa-pagination .page-link,
  .sa-pagination span {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 36px;
    height: 36px;
    padding: 0 12px;
    border-radius: 8px;
    font-size: 13px;
    text-decoration: none;
    color: #A6A39B;
    background: rgba(255,255,255,0.03);
    border: 1px solid rgba(255,255,255,0.08);
  }

  .sa-pagination .page-link:hover {
    background: rgba(124,92,255,0.12);
    color: #C4B5FD;
    border-color: rgba(124,92,255,0.25);
  }

  .sa-pagination .active span,
  .sa-pagination span[aria-current="page"] {
    background: rgba(124,92,255,0.2);
    color: #E9D5FF;
    border-color: rgba(124,92,255,0.35);
  }

  .sa-pagination .disabled span { opacity: 0.4; }
</style>
@endpush

@section('content')
<div class="sa-page-head">
  <h1>Activity log</h1>
  <p>Track sign-ins, registrations, approvals, and key events across all schools.</p>
</div>

<div class="sa-panel">
  <div class="sa-panel-head">
    <h2><i class="ri-history-line"></i> All events</h2>
    <span style="font-size:12px;color:#83807A;">{{ $logs->total() }} total</span>
  </div>
  <div class="sa-table-wrap">
    <table class="sa-table">
      <thead>
        <tr>
          <th>When</th>
          <th>School code</th>
          <th>Action</th>
          <th>Description</th>
          <th>Actor</th>
        </tr>
      </thead>
      <tbody>
        @forelse($logs as $log)
          <tr>
            <td style="white-space:nowrap;font-size:12.5px;">
              {{ $log->created_at?->format('d M Y, H:i') }}
              <div style="font-size:11px;color:#706D66;">{{ $log->created_at?->diffForHumans() }}</div>
            </td>
            <td>
              @if($log->school_code)
                <span class="sa-code">{{ $log->school_code }}</span>
              @else
                <span style="color:#706D66;">—</span>
              @endif
            </td>
            <td><strong style="color:#F5F3EE;">{{ str_replace('.', ' · ', $log->action) }}</strong></td>
            <td style="max-width:320px;">{{ $log->description ?: '—' }}</td>
            <td style="font-size:12px;color:#83807A;">
              {{ $log->actor_type ?: 'system' }}
              @if($log->actor_id)#{{ $log->actor_id }}@endif
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="5">
              <div class="sa-empty">
                <i class="ri-history-line"></i>
                No activity recorded yet.
              </div>
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
  @if($logs->hasPages())
    <div class="sa-pagination">
      {{ $logs->links() }}
    </div>
  @endif
</div>
@endsection
