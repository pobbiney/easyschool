<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>@yield('title', 'Super Admin') — EasySchool</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Sora:wght@600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet">
<style>
  * { box-sizing: border-box; margin: 0; padding: 0; }

  body {
    font-family: 'Inter', sans-serif;
    color: #ECEAE4;
    background: #0A0D0C;
    min-height: 100vh;
    position: relative;
  }

  .grid-overlay {
    position: fixed;
    inset: 0;
    background-image:
      linear-gradient(rgba(255,255,255,0.025) 1px, transparent 1px),
      linear-gradient(90deg, rgba(255,255,255,0.025) 1px, transparent 1px);
    background-size: 44px 44px;
    z-index: 0;
    pointer-events: none;
    mask-image: radial-gradient(ellipse at center, black 30%, transparent 80%);
  }

  .bg-glow-1 {
    position: fixed;
    width: 700px; height: 700px;
    background: radial-gradient(circle, rgba(124,92,255,0.12) 0%, transparent 70%);
    top: -200px; left: -150px;
    z-index: 0;
    pointer-events: none;
  }

  .bg-glow-2 {
    position: fixed;
    width: 600px; height: 600px;
    background: radial-gradient(circle, rgba(37,161,148,0.1) 0%, transparent 70%);
    bottom: -200px; right: 0;
    z-index: 0;
    pointer-events: none;
  }

  .sa-shell {
    position: relative;
    z-index: 1;
    min-height: 100vh;
    display: flex;
    flex-direction: column;
  }

  .sa-topbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px;
    padding: 16px 32px;
    border-bottom: 1px solid rgba(255,255,255,0.06);
    background: rgba(10, 13, 12, 0.75);
    backdrop-filter: blur(16px);
    position: sticky;
    top: 0;
    z-index: 100;
  }

  .sa-brand {
    display: flex;
    align-items: center;
    gap: 12px;
    text-decoration: none;
    color: inherit;
  }

  .sa-brand-icon {
    width: 42px; height: 42px;
    border-radius: 12px;
    background: linear-gradient(135deg, rgba(124,92,255,0.25), rgba(37,161,148,0.15));
    border: 1px solid rgba(124,92,255,0.4);
    display: flex; align-items: center; justify-content: center;
    font-size: 20px;
    color: #9F8CFF;
  }

  .sa-brand-text strong {
    display: block;
    font-family: 'Sora', sans-serif;
    font-size: 15px;
    color: #F5F3EE;
  }

  .sa-brand-text span {
    font-size: 11px;
    color: #83807A;
    letter-spacing: 0.04em;
    text-transform: uppercase;
  }

  .sa-nav {
    display: flex;
    align-items: center;
    gap: 6px;
    flex-wrap: wrap;
  }

  .sa-nav a {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    padding: 9px 16px;
    border-radius: 10px;
    font-size: 13px;
    font-weight: 500;
    color: #A6A39B;
    text-decoration: none;
    transition: background 0.15s, color 0.15s;
  }

  .sa-nav a:hover { background: rgba(255,255,255,0.05); color: #F5F3EE; }

  .sa-nav a.active {
    background: rgba(124,92,255,0.15);
    color: #C4B5FD;
    border: 1px solid rgba(124,92,255,0.25);
  }

  .sa-user {
    display: flex;
    align-items: center;
    gap: 12px;
  }

  .sa-user-meta { text-align: right; }
  .sa-user-meta strong { display: block; font-size: 13px; color: #F5F3EE; }
  .sa-user-meta span { font-size: 11px; color: #83807A; }

  .sa-btn-logout {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 9px 16px;
    border-radius: 10px;
    border: 1px solid rgba(255,255,255,0.1);
    background: rgba(255,255,255,0.04);
    color: #ECEAE4;
    font-size: 13px;
    font-weight: 500;
    cursor: pointer;
    transition: background 0.15s, border-color 0.15s;
  }

  .sa-btn-logout:hover {
    background: rgba(220, 53, 69, 0.12);
    border-color: rgba(220, 53, 69, 0.35);
    color: #ffb4bc;
  }

  .sa-main {
    flex: 1;
    padding: 32px;
    max-width: 1400px;
    width: 100%;
    margin: 0 auto;
  }

  .sa-page-head {
    margin-bottom: 28px;
  }

  .sa-page-head h1 {
    font-family: 'Sora', sans-serif;
    font-weight: 700;
    font-size: 28px;
    color: #F5F3EE;
    margin-bottom: 6px;
  }

  .sa-page-head p {
    font-size: 14px;
    color: #83807A;
  }

  .sa-flash {
    padding: 14px 18px;
    border-radius: 12px;
    margin-bottom: 24px;
    font-size: 14px;
    display: flex;
    align-items: center;
    gap: 10px;
  }

  .sa-flash.success {
    background: rgba(37,161,148,0.12);
    border: 1px solid rgba(37,161,148,0.35);
    color: #7ee8d8;
  }

  .sa-flash.error {
    background: rgba(220, 53, 69, 0.12);
    border: 1px solid rgba(220, 53, 69, 0.35);
    color: #ffb4bc;
  }

  .sa-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 16px;
    margin-bottom: 28px;
  }

  .sa-stat {
    padding: 22px 24px;
    border-radius: 16px;
    background: rgba(17, 20, 19, 0.75);
    border: 1px solid rgba(255,255,255,0.07);
    backdrop-filter: blur(12px);
    position: relative;
    overflow: hidden;
  }

  .sa-stat::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 3px;
    background: var(--accent, #7C5CFF);
    opacity: 0.8;
  }

  .sa-stat.teal { --accent: #25A194; }
  .sa-stat.purple { --accent: #7C5CFF; }
  .sa-stat.amber { --accent: #F59E0B; }
  .sa-stat.rose { --accent: #F43F5E; }

  .sa-stat-label {
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: #83807A;
    margin-bottom: 8px;
  }

  .sa-stat-value {
    font-family: 'Sora', sans-serif;
    font-size: 32px;
    font-weight: 700;
    color: #F5F3EE;
    line-height: 1;
  }

  .sa-stat-icon {
    position: absolute;
    right: 20px;
    top: 50%;
    transform: translateY(-50%);
    font-size: 36px;
    opacity: 0.12;
    color: var(--accent, #7C5CFF);
  }

  .sa-panel {
    border-radius: 18px;
    background: rgba(17, 20, 19, 0.82);
    border: 1px solid rgba(255,255,255,0.07);
    backdrop-filter: blur(16px);
    overflow: hidden;
    margin-bottom: 24px;
  }

  .sa-panel-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    padding: 18px 24px;
    border-bottom: 1px solid rgba(255,255,255,0.06);
  }

  .sa-panel-head h2 {
    font-family: 'Sora', sans-serif;
    font-size: 16px;
    font-weight: 700;
    color: #F5F3EE;
    display: flex;
    align-items: center;
    gap: 10px;
  }

  .sa-panel-head h2 i { color: #9F8CFF; font-size: 18px; }

  .sa-table-wrap { overflow-x: auto; }

  .sa-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 13.5px;
  }

  .sa-table th {
    text-align: left;
    padding: 12px 20px;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: #83807A;
    background: rgba(255,255,255,0.02);
    border-bottom: 1px solid rgba(255,255,255,0.06);
  }

  .sa-table td {
    padding: 16px 20px;
    border-bottom: 1px solid rgba(255,255,255,0.04);
    color: #C9C6BE;
    vertical-align: middle;
  }

  .sa-table tbody tr:hover td {
    background: rgba(255,255,255,0.02);
  }

  .sa-table tbody tr:last-child td { border-bottom: none; }

  .sa-code {
    font-family: ui-monospace, monospace;
    font-size: 12px;
    padding: 4px 10px;
    border-radius: 6px;
    background: rgba(124,92,255,0.12);
    border: 1px solid rgba(124,92,255,0.25);
    color: #C4B5FD;
  }

  .sa-badge {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 4px 10px;
    border-radius: 100px;
    font-size: 11.5px;
    font-weight: 600;
    text-transform: capitalize;
  }

  .sa-badge.approved { background: rgba(37,161,148,0.15); color: #5eead4; border: 1px solid rgba(37,161,148,0.3); }
  .sa-badge.pending { background: rgba(245,158,11,0.15); color: #fcd34d; border: 1px solid rgba(245,158,11,0.3); }
  .sa-badge.suspended { background: rgba(244,63,94,0.15); color: #fda4af; border: 1px solid rgba(244,63,94,0.3); }
  .sa-badge.rejected { background: rgba(255,255,255,0.06); color: #A6A39B; border: 1px solid rgba(255,255,255,0.1); }

  .sa-actions {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 8px;
    flex-wrap: wrap;
  }

  .sa-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 7px 14px;
    border-radius: 8px;
    font-size: 12.5px;
    font-weight: 600;
    border: none;
    cursor: pointer;
    text-decoration: none;
    transition: transform 0.12s, box-shadow 0.12s;
  }

  .sa-btn:hover { transform: translateY(-1px); }

  .sa-btn-primary {
    background: linear-gradient(135deg, #8B72FF, #5E45D6);
    color: #fff;
  }

  .sa-btn-primary:hover { box-shadow: 0 6px 16px rgba(124,92,255,0.35); color: #fff; }

  .sa-btn-success {
    background: linear-gradient(135deg, #2DBFAE, #1C8078);
    color: #fff;
  }

  .sa-btn-success:hover { box-shadow: 0 6px 16px rgba(37,161,148,0.3); color: #fff; }

  .sa-btn-danger-outline {
    background: transparent;
    border: 1px solid rgba(244,63,94,0.4);
    color: #fda4af;
  }

  .sa-btn-danger-outline:hover {
    background: rgba(244,63,94,0.1);
    color: #fda4af;
  }

  .sa-btn-danger {
    background: rgba(244,63,94,0.2);
    border: 1px solid rgba(244,63,94,0.4);
    color: #fda4af;
  }

  .sa-empty {
    padding: 48px 24px;
    text-align: center;
    color: #83807A;
    font-size: 14px;
  }

  .sa-empty i {
    font-size: 40px;
    display: block;
    margin-bottom: 12px;
    opacity: 0.35;
  }

  .sa-activity-list { list-style: none; }

  .sa-activity-item {
    padding: 16px 24px;
    border-bottom: 1px solid rgba(255,255,255,0.04);
    display: flex;
    gap: 16px;
    align-items: flex-start;
  }

  .sa-activity-item:last-child { border-bottom: none; }

  .sa-activity-icon {
    width: 36px; height: 36px;
    border-radius: 10px;
    background: rgba(124,92,255,0.12);
    border: 1px solid rgba(124,92,255,0.2);
    display: flex; align-items: center; justify-content: center;
    color: #9F8CFF;
    flex-shrink: 0;
    font-size: 16px;
  }

  .sa-activity-body { flex: 1; min-width: 0; }

  .sa-activity-top {
    display: flex;
    justify-content: space-between;
    gap: 12px;
    align-items: flex-start;
    margin-bottom: 4px;
  }

  .sa-activity-top strong {
    font-size: 13.5px;
    color: #F5F3EE;
    font-weight: 600;
  }

  .sa-activity-top time {
    font-size: 11.5px;
    color: #706D66;
    white-space: nowrap;
  }

  .sa-activity-desc {
    font-size: 12.5px;
    color: #83807A;
    line-height: 1.5;
  }

  .sa-count-pill {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 28px;
    padding: 2px 8px;
    border-radius: 100px;
    background: rgba(255,255,255,0.06);
    font-size: 12px;
    font-weight: 600;
    color: #ECEAE4;
  }

  @media (max-width: 768px) {
    .sa-topbar { flex-wrap: wrap; padding: 14px 18px; }
    .sa-main { padding: 20px 16px; }
    .sa-user-meta { display: none; }
    .sa-nav { order: 3; width: 100%; }
  }

  .sa-form-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 16px;
  }

  .sa-form-grid .full { grid-column: 1 / -1; }

  .sa-field label {
    display: block;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: #B7B4AC;
    margin-bottom: 7px;
  }

  .sa-input {
    width: 100%;
    background: rgba(255,255,255,0.03);
    border: 1px solid rgba(255,255,255,0.09);
    border-radius: 10px;
    padding: 11px 14px;
    color: #F5F3EE;
    font-size: 14px;
    font-family: 'Inter', sans-serif;
  }

  .sa-input:focus {
    outline: none;
    border-color: #7C5CFF;
    background: rgba(124,92,255,0.06);
  }

  .sa-input::placeholder { color: #706D66; }

  .sa-input[disabled] {
    opacity: 0.65;
    cursor: not-allowed;
  }

  .sa-field-error {
    display: block;
    margin-top: 5px;
    font-size: 11.5px;
    color: #ff8a96;
  }

  .sa-profile-card {
    display: flex;
    align-items: center;
    gap: 18px;
    padding: 24px;
  }

  .sa-avatar {
    width: 72px; height: 72px;
    border-radius: 50%;
    background: linear-gradient(135deg, rgba(124,92,255,0.25), rgba(37,161,148,0.15));
    border: 1.5px solid rgba(124,92,255,0.45);
    display: flex; align-items: center; justify-content: center;
    font-size: 28px;
    color: #9F8CFF;
    flex-shrink: 0;
  }

  .sa-profile-meta h2 {
    font-family: 'Sora', sans-serif;
    font-size: 20px;
    color: #F5F3EE;
    margin-bottom: 4px;
  }

  .sa-profile-meta p {
    font-size: 13px;
    color: #83807A;
    margin: 0;
  }

  .sa-panel-body { padding: 24px; }

  @media (max-width: 768px) {
    .sa-form-grid { grid-template-columns: 1fr; }
  }
</style>
@stack('styles')
</head>
<body>

<div class="grid-overlay"></div>
<div class="bg-glow-1"></div>
<div class="bg-glow-2"></div>

<div class="sa-shell">
  <header class="sa-topbar">
    <a href="{{ route('super-admin.dashboard') }}" class="sa-brand">
      <div class="sa-brand-icon"><i class="ri-shield-star-line"></i></div>
      <div class="sa-brand-text">
        <strong>EasySchool</strong>
        <span>Super Admin</span>
      </div>
    </a>

    <nav class="sa-nav">
      <a href="{{ route('super-admin.dashboard') }}" class="{{ request()->routeIs('super-admin.dashboard') ? 'active' : '' }}">
        <i class="ri-dashboard-3-line"></i> Dashboard
      </a>
      <a href="{{ route('super-admin.registrations') }}" class="{{ request()->routeIs('super-admin.registrations*') ? 'active' : '' }}">
        <i class="ri-user-received-2-line"></i> Registrations
      </a>
      <a href="{{ route('super-admin.activity') }}" class="{{ request()->routeIs('super-admin.activity') ? 'active' : '' }}">
        <i class="ri-history-line"></i> Activity
      </a>
      <a href="{{ route('super-admin.profile') }}" class="{{ request()->routeIs('super-admin.profile*') ? 'active' : '' }}">
        <i class="ri-user-settings-line"></i> Profile
      </a>
      <a href="{{ route('super-admin.admins') }}" class="{{ request()->routeIs('super-admin.admins*') ? 'active' : '' }}">
        <i class="ri-shield-user-line"></i> Super Admins
      </a>
    </nav>

    <div class="sa-user">
      <a href="{{ route('super-admin.profile') }}" class="sa-user-meta" style="text-decoration:none;">
        <strong>{{ auth('super_admin')->user()->name ?? 'Super Admin' }}</strong>
        <span>View profile</span>
      </a>
      <form method="POST" action="{{ route('super-admin.logout') }}">
        @csrf
        <button type="submit" class="sa-btn-logout">
          <i class="ri-logout-box-r-line"></i> Logout
        </button>
      </form>
    </div>
  </header>

  <main class="sa-main">
    @if(session('message_success'))
      <div class="sa-flash success"><i class="ri-checkbox-circle-fill"></i> {{ session('message_success') }}</div>
    @endif
    @if(session('message_error'))
      <div class="sa-flash error"><i class="ri-error-warning-fill"></i> {{ session('message_error') }}</div>
    @endif

    @yield('content')
  </main>
</div>

@stack('scripts')
</body>
</html>
