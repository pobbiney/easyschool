<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Super Admin — EasySchool</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Sora:wght@600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet">
<style>
  * { box-sizing: border-box; margin: 0; padding: 0; }
  html, body { height: 100%; }

  body {
    font-family: 'Inter', sans-serif;
    color: #ECEAE4;
    background: #0A0D0C;
    min-height: 100vh;
    position: relative;
    overflow-x: hidden;
    display: flex;
    align-items: center;
  }

  .grid-overlay {
    position: absolute;
    inset: 0;
    background-image:
      linear-gradient(rgba(255,255,255,0.025) 1px, transparent 1px),
      linear-gradient(90deg, rgba(255,255,255,0.025) 1px, transparent 1px);
    background-size: 44px 44px;
    z-index: 0;
    mask-image: radial-gradient(ellipse at center, black 30%, transparent 75%);
  }

  .bg-glow-1 {
    position: absolute;
    width: 750px; height: 750px;
    background: radial-gradient(circle, rgba(124,92,255,0.14) 0%, transparent 70%);
    top: -220px; left: -180px;
    z-index: 0;
  }

  .bg-glow-2 {
    position: absolute;
    width: 620px; height: 620px;
    background: radial-gradient(circle, rgba(37,161,148,0.12) 0%, transparent 70%);
    bottom: -260px; right: 6%;
    z-index: 0;
  }

  .page {
    position: relative;
    z-index: 1;
    width: 100%;
    max-width: 1440px;
    margin: 0 auto;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 48px;
    padding: 56px 64px;
    flex-wrap: wrap;
  }

  .hero { max-width: 580px; flex: 1; min-width: 340px; }

  .eyebrow {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    font-size: 12.5px;
    font-weight: 600;
    letter-spacing: 0.06em;
    text-transform: uppercase;
    color: #9F8CFF;
    background: rgba(124,92,255,0.12);
    border: 1px solid rgba(124,92,255,0.35);
    padding: 6px 14px;
    border-radius: 100px;
    margin-bottom: 22px;
  }

  .eyebrow .pulse-dot {
    width: 6px; height: 6px;
    border-radius: 50%;
    background: #7C5CFF;
    box-shadow: 0 0 0 0 rgba(124,92,255,0.6);
    animation: pulse 2s infinite;
  }

  @keyframes pulse {
    0% { box-shadow: 0 0 0 0 rgba(124,92,255,0.5); }
    70% { box-shadow: 0 0 0 8px rgba(124,92,255,0); }
    100% { box-shadow: 0 0 0 0 rgba(124,92,255,0); }
  }

  .hero h1 {
    font-family: 'Sora', sans-serif;
    font-weight: 800;
    font-size: 54px;
    line-height: 1.14;
    color: #F5F3EE;
    letter-spacing: -0.01em;
  }

  .hero h1 .accent { color: #9F8CFF; }

  .hero p {
    font-size: 16px;
    line-height: 1.75;
    color: #A6A39B;
    margin-top: 22px;
    max-width: 480px;
  }

  .feature-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 14px;
    margin-top: 36px;
  }

  .feature {
    padding: 16px 18px;
    border-radius: 14px;
    background: rgba(255,255,255,0.03);
    border: 1px solid rgba(255,255,255,0.07);
  }

  .feature i {
    font-size: 20px;
    color: #7C5CFF;
    margin-bottom: 10px;
    display: block;
  }

  .feature strong {
    display: block;
    font-size: 13px;
    color: #F5F3EE;
    margin-bottom: 4px;
  }

  .feature span {
    font-size: 12px;
    color: #83807A;
    line-height: 1.5;
  }

  .card-wrap {
    position: relative;
    width: 460px;
    flex-shrink: 0;
  }

  .card-border-glow {
    position: absolute;
    inset: -1px;
    border-radius: 22px;
    background: linear-gradient(135deg, rgba(124,92,255,0.5), rgba(37,161,148,0.25), rgba(124,92,255,0.15));
    z-index: 0;
  }

  .card {
    position: relative;
    z-index: 1;
    background: rgba(17, 20, 19, 0.88);
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    border-radius: 21px;
    padding: 42px 38px 30px;
    box-shadow: 0 30px 70px rgba(0,0,0,0.55);
    text-align: center;
  }

  .logo {
    width: 80px; height: 80px;
    border-radius: 50%;
    background: linear-gradient(135deg, rgba(124,92,255,0.2), rgba(37,161,148,0.08));
    border: 1.5px solid rgba(124,92,255,0.55);
    display: flex; align-items: center; justify-content: center;
    margin: 0 auto 18px;
    font-size: 32px;
    color: #9F8CFF;
  }

  .card h2 {
    font-family: 'Sora', sans-serif;
    font-weight: 700;
    font-size: 21px;
    color: #F5F3EE;
    margin-bottom: 4px;
  }

  .card .sub {
    font-size: 13px;
    color: #83807A;
    margin-bottom: 28px;
  }

  .alert-error {
    background: rgba(220, 53, 69, 0.12);
    border: 1px solid rgba(220, 53, 69, 0.35);
    border-radius: 10px;
    padding: 12px 14px;
    margin-bottom: 20px;
    font-size: 13px;
    color: #ffb4bc;
    text-align: left;
  }

  .field { text-align: left; margin-bottom: 20px; }

  .field label {
    display: block;
    font-size: 11.5px;
    font-weight: 600;
    color: #B7B4AC;
    margin-bottom: 8px;
    letter-spacing: 0.03em;
    text-transform: uppercase;
  }

  .input-wrap {
    display: flex;
    align-items: center;
    gap: 10px;
    background: rgba(255,255,255,0.03);
    border: 1px solid rgba(255,255,255,0.09);
    border-radius: 10px;
    padding: 12px 14px;
    transition: border-color 0.15s ease, background 0.15s ease;
  }

  .input-wrap:focus-within {
    border-color: #7C5CFF;
    background: rgba(124,92,255,0.06);
  }

  .input-wrap i { color: #9F8CFF; font-size: 16px; flex-shrink: 0; }

  .input-wrap input {
    flex: 1;
    background: transparent;
    border: none;
    outline: none;
    color: #F5F3EE;
    font-size: 14px;
    font-family: 'Inter', sans-serif;
  }

  .input-wrap input::placeholder { color: #706D66; }

  .toggle-eye {
    background: none; border: none; cursor: pointer;
    padding: 0; display: flex; opacity: 0.65; color: #ECEAE4;
  }
  .toggle-eye:hover { opacity: 1; }

  button.submit {
    width: 100%;
    height: 48px;
    background: linear-gradient(135deg, #8B72FF, #5E45D6);
    color: #fff;
    border: none;
    border-radius: 11px;
    font-family: 'Sora', sans-serif;
    font-weight: 700;
    font-size: 14.5px;
    cursor: pointer;
    display: flex; align-items: center; justify-content: center; gap: 8px;
    transition: transform 0.15s ease, box-shadow 0.15s ease;
  }

  button.submit:hover {
    transform: translateY(-1px);
    box-shadow: 0 10px 24px rgba(124,92,255,0.35);
  }

  .footer-links {
    margin-top: 22px;
    font-size: 12.5px;
    color: #8B887F;
    line-height: 2;
  }

  .footer-links a {
    color: #25A194;
    text-decoration: none;
    font-weight: 500;
  }

  .footer-links a:hover { text-decoration: underline; }

  .footer-links .divider { color: #4B4941; margin: 0 8px; }

  .copyright { margin-top: 18px; font-size: 11px; color: #605D57; }

  @media (max-width: 980px) {
    .page { flex-direction: column; padding: 40px 22px; }
    .hero h1 { font-size: 38px; }
    .feature-grid { grid-template-columns: 1fr; }
    .card-wrap { width: 100%; max-width: 400px; }
  }
</style>
</head>
<body>

<div class="grid-overlay"></div>
<div class="bg-glow-1"></div>
<div class="bg-glow-2"></div>

<div class="page">

  <div class="hero">
    <div class="eyebrow"><span class="pulse-dot"></span> Platform control centre</div>

    <h1>Manage every<br>school from<br><span class="accent">one place.</span></h1>
    <p>Approve registrations, assign school codes, monitor activity, enter any school’s dashboard, and keep the entire EasySchool platform running smoothly.</p>

    <div class="feature-grid">
      <div class="feature">
        <i class="ri-building-4-line"></i>
        <strong>All schools</strong>
        <span>View and manage every tenant on the platform.</span>
      </div>
      <div class="feature">
        <i class="ri-user-received-2-line"></i>
        <strong>Approvals</strong>
        <span>Review pending registrations and activate schools.</span>
      </div>
      <div class="feature">
        <i class="ri-eye-line"></i>
        <strong>Enter school</strong>
        <span>Step into any school’s admin view when needed.</span>
      </div>
      <div class="feature">
        <i class="ri-history-line"></i>
        <strong>Activity log</strong>
        <span>Track sign-ins, registrations, and key events.</span>
      </div>
    </div>
  </div>

  <div class="card-wrap">
    <div class="card-border-glow"></div>
    <div class="card">
      <div class="logo"><i class="ri-shield-star-line"></i></div>
      <h2>Super Admin</h2>
      <p class="sub">Sign in to manage the EasySchool platform</p>

      @if (session('login_error_message'))
        <div class="alert-error">{{ session('login_error_message') }}</div>
      @endif

      <form method="POST" action="{{ route('super-admin.login.process') }}">
        @csrf

        <div class="field">
          <label for="email">Email address</label>
          <div class="input-wrap">
            <i class="ri-mail-line"></i>
            <input type="email" id="email" name="email" value="{{ old('email') }}" placeholder="owner@easyschool.local" required autofocus>
          </div>
          @error('email')<small style="color:#ff8a96;font-size:11.5px;">{{ $message }}</small>@enderror
        </div>

        <div class="field">
          <label for="password">Password</label>
          <div class="input-wrap">
            <i class="ri-lock-line"></i>
            <input type="password" id="password" name="password" placeholder="Enter your password" required>
            <button type="button" class="toggle-eye" id="toggleEye" aria-label="Show password">
              <i class="ri-eye-line" id="eyeIcon"></i>
            </button>
          </div>
          @error('password')<small style="color:#ff8a96;font-size:11.5px;">{{ $message }}</small>@enderror
        </div>

        <button type="submit" class="submit">
          Sign in
          <i class="ri-arrow-right-line"></i>
        </button>
      </form>

      <div class="footer-links">
        <a href="{{ route('admin-login') }}">Staff login</a>
        <span class="divider">·</span>
        <a href="{{ route('register-school') }}">Register a school</a>
      </div>

      <div class="copyright">© 2026 EasySchool. All rights reserved.</div>
    </div>
  </div>

</div>

<script>
  const toggleEye = document.getElementById('toggleEye');
  const passwordInput = document.getElementById('password');
  const eyeIcon = document.getElementById('eyeIcon');

  toggleEye.addEventListener('click', function () {
    const isPassword = passwordInput.type === 'password';
    passwordInput.type = isPassword ? 'text' : 'password';
    eyeIcon.className = isPassword ? 'ri-eye-off-line' : 'ri-eye-line';
    toggleEye.setAttribute('aria-label', isPassword ? 'Hide password' : 'Show password');
  });
</script>

</body>
</html>
