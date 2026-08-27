@extends('layouts.super-admin')

@section('title', 'Super Admins')

@section('content')
<div class="sa-page-head">
  <h1>Super admin accounts</h1>
  <p>Create additional platform administrators who can manage schools and approvals.</p>
</div>

<div class="sa-panel">
  <div class="sa-panel-head">
    <h2><i class="ri-user-add-line"></i> Create super admin</h2>
  </div>
  <div class="sa-panel-body">
    <form method="POST" action="{{ route('super-admin.admins.store') }}">
      @csrf
      <div class="sa-form-grid">
        <div class="sa-field">
          <label for="name">Full name</label>
          <input type="text" id="name" name="name" class="sa-input" value="{{ old('name') }}" required placeholder="Jane Smith">
          @error('name')<span class="sa-field-error">{{ $message }}</span>@enderror
        </div>
        <div class="sa-field">
          <label for="email">Email address</label>
          <input type="email" id="email" name="email" class="sa-input" value="{{ old('email') }}" required placeholder="admin@example.com">
          @error('email')<span class="sa-field-error">{{ $message }}</span>@enderror
        </div>
        <div class="sa-field">
          <label for="password">Password</label>
          <input type="password" id="password" name="password" class="sa-input" required placeholder="Minimum 8 characters">
          @error('password')<span class="sa-field-error">{{ $message }}</span>@enderror
        </div>
        <div class="sa-field">
          <label for="password_confirmation">Confirm password</label>
          <input type="password" id="password_confirmation" name="password_confirmation" class="sa-input" required>
        </div>
      </div>
      <div style="margin-top:20px;">
        <button type="submit" class="sa-btn sa-btn-success">
          <i class="ri-user-add-line"></i> Create account
        </button>
      </div>
    </form>
  </div>
</div>

<div class="sa-panel" style="margin-top:24px;">
  <div class="sa-panel-head">
    <h2><i class="ri-team-line"></i> All super admins</h2>
    <span style="font-size:12px;color:#83807A;">{{ $admins->count() }} account(s)</span>
  </div>
  <div class="sa-table-wrap">
    <table class="sa-table">
      <thead>
        <tr>
          <th>Name</th>
          <th>Email</th>
          <th>Status</th>
          <th>Created</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        @foreach($admins as $admin)
          <tr>
            <td>
              <strong style="color:#F5F3EE;">{{ $admin->name }}</strong>
              @if($admin->id === $currentAdminId)
                <span class="sa-badge approved" style="margin-left:8px;font-size:10px;">You</span>
              @endif
            </td>
            <td>{{ $admin->email }}</td>
            <td>
              <span class="sa-badge {{ $admin->isActive() ? 'approved' : 'suspended' }}">
                {{ $admin->status }}
              </span>
            </td>
            <td style="font-size:12.5px;color:#83807A;">
              {{ $admin->created_at?->format('d M Y') }}
            </td>
            <td>
              <div class="sa-actions">
                @if($admin->id !== $currentAdminId)
                  <form method="POST" action="{{ route('super-admin.admins.toggle-status', $admin) }}">
                    @csrf
                    <button type="submit" class="sa-btn {{ $admin->isActive() ? 'sa-btn-danger-outline' : 'sa-btn-success' }}">
                      @if($admin->isActive())
                        <i class="ri-forbid-line"></i> Deactivate
                      @else
                        <i class="ri-check-line"></i> Activate
                      @endif
                    </button>
                  </form>
                @else
                  <a href="{{ route('super-admin.profile') }}" class="sa-btn sa-btn-primary">
                    <i class="ri-user-settings-line"></i> Profile
                  </a>
                @endif
              </div>
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>
@endsection
