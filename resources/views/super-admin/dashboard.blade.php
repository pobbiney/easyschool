@extends('layouts.super-admin')

@section('title', 'Dashboard')

@section('content')
<div class="sa-page-head">
  <h1>Platform overview</h1>
  <p>Manage all schools, approve registrations, and monitor activity across EasySchool.</p>
</div>

<div class="sa-stats">
  <div class="sa-stat purple">
    <div class="sa-stat-label">Total schools</div>
    <div class="sa-stat-value">{{ $schools->count() }}</div>
    <i class="sa-stat-icon ri-building-4-line"></i>
  </div>
  <div class="sa-stat teal">
    <div class="sa-stat-label">Approved</div>
    <div class="sa-stat-value">{{ $approvedCount }}</div>
    <i class="sa-stat-icon ri-checkbox-circle-line"></i>
  </div>
  <div class="sa-stat amber">
    <div class="sa-stat-label">Pending approval</div>
    <div class="sa-stat-value">{{ $pendingCount }}</div>
    <i class="sa-stat-icon ri-time-line"></i>
  </div>
  <div class="sa-stat rose">
    <div class="sa-stat-label">Suspended</div>
    <div class="sa-stat-value">{{ $schools->where('status', 'suspended')->count() }}</div>
    <i class="sa-stat-icon ri-forbid-line"></i>
  </div>
</div>

<div class="sa-panel">
  <div class="sa-panel-head">
    <h2><i class="ri-building-2-line"></i> All schools</h2>
    @if($pendingCount > 0)
      <a href="{{ route('super-admin.registrations') }}" class="sa-btn sa-btn-primary">
        <i class="ri-user-add-line"></i> {{ $pendingCount }} pending
      </a>
    @endif
  </div>
  <div class="sa-table-wrap">
    <table class="sa-table">
      <thead>
        <tr>
          <th>School code</th>
          <th>Name</th>
          <th>Status</th>
          <th>Students</th>
          <th>Staff</th>
          <th>Users</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        @forelse($schools as $school)
          <tr>
            <td>
              @if($school->code)
                <span class="sa-code">{{ $school->code }}</span>
              @else
                <span style="color:#706D66;">—</span>
              @endif
            </td>
            <td><strong style="color:#F5F3EE;">{{ $school->name }}</strong></td>
            <td>
              <span class="sa-badge {{ $school->status }}">
                @if($school->status === 'approved')<i class="ri-check-line"></i>@endif
                @if($school->status === 'pending')<i class="ri-time-line"></i>@endif
                @if($school->status === 'suspended')<i class="ri-forbid-line"></i>@endif
                {{ $school->status }}
              </span>
            </td>
            <td><span class="sa-count-pill">{{ $school->students_count ?? 0 }}</span></td>
            <td><span class="sa-count-pill">{{ $school->staff_count ?? 0 }}</span></td>
            <td><span class="sa-count-pill">{{ $school->users_count ?? 0 }}</span></td>
            <td>
              <div class="sa-actions">
                @if(in_array($school->status, ['approved', 'suspended'], true))
                  <form method="POST" action="{{ route('super-admin.schools.enter', $school) }}">
                    @csrf
                    <button type="submit" class="sa-btn sa-btn-primary">
                      <i class="ri-login-box-line"></i> Enter
                    </button>
                  </form>
                  @if($school->isSuspended())
                    <form method="POST" action="{{ route('super-admin.schools.reactivate', $school) }}">
                      @csrf
                      <button type="submit" class="sa-btn sa-btn-success">
                        <i class="ri-play-circle-line"></i> Reactivate
                      </button>
                    </form>
                  @else
                    <form method="POST" action="{{ route('super-admin.schools.suspend', $school) }}" onsubmit="return confirm('Suspend this school? Staff and parents will not be able to sign in.')">
                      @csrf
                      <button type="submit" class="sa-btn sa-btn-danger-outline">
                        <i class="ri-pause-circle-line"></i> Suspend
                      </button>
                    </form>
                  @endif
                @elseif($school->isPending())
                  <a href="{{ route('super-admin.registrations') }}" class="sa-btn sa-btn-success">
                    <i class="ri-check-double-line"></i> Review
                  </a>
                @endif
              </div>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="7">
              <div class="sa-empty">
                <i class="ri-building-line"></i>
                No schools registered yet.
              </div>
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

<div class="sa-panel">
  <div class="sa-panel-head">
    <h2><i class="ri-pulse-line"></i> Recent activity</h2>
    <a href="{{ route('super-admin.activity') }}" class="sa-btn sa-btn-danger-outline" style="border-color:rgba(124,92,255,0.35);color:#C4B5FD;">
      View all
    </a>
  </div>
  <ul class="sa-activity-list">
    @forelse($recentActivity as $log)
      <li class="sa-activity-item">
        <div class="sa-activity-icon">
          @if(str_contains($log->action, 'login'))
            <i class="ri-login-circle-line"></i>
          @elseif(str_contains($log->action, 'approved'))
            <i class="ri-check-double-line"></i>
          @elseif(str_contains($log->action, 'registration'))
            <i class="ri-file-add-line"></i>
          @elseif(str_contains($log->action, 'student'))
            <i class="ri-graduation-cap-line"></i>
          @else
            <i class="ri-record-circle-line"></i>
          @endif
        </div>
        <div class="sa-activity-body">
          <div class="sa-activity-top">
            <strong>{{ str_replace('.', ' · ', $log->action) }}</strong>
            <time>{{ $log->created_at?->diffForHumans() }}</time>
          </div>
          <div class="sa-activity-desc">
            @if($log->school_code)
              <span class="sa-code" style="font-size:11px;padding:2px 8px;margin-right:6px;">{{ $log->school_code }}</span>
            @endif
            {{ $log->description ?: 'No description' }}
          </div>
        </div>
      </li>
    @empty
      <li class="sa-empty">
        <i class="ri-history-line"></i>
        No activity logged yet.
      </li>
    @endforelse
  </ul>
</div>
@endsection
