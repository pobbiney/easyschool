@extends('layouts.super-admin')

@section('title', 'My Profile')

@section('content')
<div class="sa-page-head">
  <h1>My profile</h1>
  <p>View your super admin account details and update your password.</p>
</div>

<div class="sa-panel">
  <div class="sa-profile-card">
    <div class="sa-avatar"><i class="ri-shield-star-line"></i></div>
    <div class="sa-profile-meta">
      <h2>{{ $admin->name }}</h2>
      <p>{{ $admin->email }}</p>
      <p style="margin-top:8px;">
        <span class="sa-badge {{ $admin->isActive() ? 'approved' : 'suspended' }}">
          {{ $admin->status }}
        </span>
        <span style="margin-left:8px;color:#706D66;font-size:12px;">
          Member since {{ $admin->created_at?->format('d M Y') }}
        </span>
      </p>
    </div>
  </div>
</div>

<div class="sa-panel" style="margin-top:24px;">
  <div class="sa-panel-head">
    <h2><i class="ri-lock-password-line"></i> Change password</h2>
  </div>
  <div class="sa-panel-body">
    <form method="POST" action="{{ route('super-admin.profile.password') }}">
      @csrf
      <div class="sa-form-grid">
        <div class="sa-field full">
          <label for="current_password">Current password</label>
          <input type="password" id="current_password" name="current_password" class="sa-input" required autocomplete="current-password">
          @error('current_password')<span class="sa-field-error">{{ $message }}</span>@enderror
        </div>
        <div class="sa-field">
          <label for="new_password">New password</label>
          <input type="password" id="new_password" name="new_password" class="sa-input" required autocomplete="new-password" placeholder="Minimum 8 characters">
          @error('new_password')<span class="sa-field-error">{{ $message }}</span>@enderror
        </div>
        <div class="sa-field">
          <label for="new_password_confirmation">Confirm new password</label>
          <input type="password" id="new_password_confirmation" name="new_password_confirmation" class="sa-input" required autocomplete="new-password">
        </div>
      </div>
      <div style="margin-top:20px;">
        <button type="submit" class="sa-btn sa-btn-primary">
          <i class="ri-save-line"></i> Update password
        </button>
      </div>
    </form>
  </div>
</div>
@endsection
