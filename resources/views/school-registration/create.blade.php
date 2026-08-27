<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Register Your School — EasySchool</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Sora:wght@600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet">
<style>
  * { box-sizing: border-box; margin: 0; padding: 0; }
  html, body { min-height: 100%; }

  body {
    font-family: 'Inter', sans-serif;
    color: #ECEAE4;
    background: #0A0D0C;
    min-height: 100vh;
    position: relative;
    overflow-x: hidden;
  }

  .grid-overlay {
    position: fixed;
    inset: 0;
    background-image:
      linear-gradient(rgba(255,255,255,0.025) 1px, transparent 1px),
      linear-gradient(90deg, rgba(255,255,255,0.025) 1px, transparent 1px);
    background-size: 44px 44px;
    z-index: 0;
    mask-image: radial-gradient(ellipse at center, black 30%, transparent 75%);
    pointer-events: none;
  }

  .bg-glow-1 {
    position: fixed;
    width: 750px; height: 750px;
    background: radial-gradient(circle, rgba(37,161,148,0.16) 0%, transparent 70%);
    top: -220px; left: -180px;
    z-index: 0;
    pointer-events: none;
  }

  .bg-glow-2 {
    position: fixed;
    width: 620px; height: 620px;
    background: radial-gradient(circle, rgba(124,92,255,0.09) 0%, transparent 70%);
    bottom: -260px; right: 6%;
    z-index: 0;
    pointer-events: none;
  }

  .page {
    position: relative;
    z-index: 1;
    width: 100%;
    max-width: 1320px;
    margin: 0 auto;
    display: grid;
    grid-template-columns: minmax(300px, 1fr) minmax(380px, 560px);
    gap: 48px;
    padding: 48px 56px 64px;
    align-items: start;
  }

  .hero { padding-top: 24px; }

  .eyebrow {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    font-size: 12.5px;
    font-weight: 600;
    letter-spacing: 0.06em;
    text-transform: uppercase;
    color: #25A194;
    background: rgba(37,161,148,0.1);
    border: 1px solid rgba(37,161,148,0.3);
    padding: 6px 14px;
    border-radius: 100px;
    margin-bottom: 22px;
  }

  .eyebrow .pulse-dot {
    width: 6px; height: 6px;
    border-radius: 50%;
    background: #25A194;
    box-shadow: 0 0 0 0 rgba(37,161,148,0.6);
    animation: pulse 2s infinite;
  }

  @keyframes pulse {
    0% { box-shadow: 0 0 0 0 rgba(37,161,148,0.5); }
    70% { box-shadow: 0 0 0 8px rgba(37,161,148,0); }
    100% { box-shadow: 0 0 0 0 rgba(37,161,148,0); }
  }

  .hero h1 {
    font-family: 'Sora', sans-serif;
    font-weight: 800;
    font-size: clamp(34px, 4vw, 50px);
    line-height: 1.14;
    color: #F5F3EE;
    letter-spacing: -0.01em;
  }

  .hero h1 .accent { color: #25A194; }

  .hero p {
    font-size: 16px;
    line-height: 1.75;
    color: #A6A39B;
    margin-top: 20px;
    max-width: 480px;
  }

  .steps {
    margin-top: 36px;
    display: flex;
    flex-direction: column;
    gap: 16px;
  }

  .step {
    display: flex;
    gap: 14px;
    align-items: flex-start;
    padding: 16px 18px;
    border-radius: 14px;
    background: rgba(255,255,255,0.03);
    border: 1px solid rgba(255,255,255,0.07);
  }

  .step-num {
    width: 32px; height: 32px;
    border-radius: 10px;
    background: rgba(37,161,148,0.15);
    border: 1px solid rgba(37,161,148,0.35);
    color: #25A194;
    font-family: 'Sora', sans-serif;
    font-weight: 700;
    font-size: 14px;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
  }

  .step strong {
    display: block;
    font-size: 14px;
    color: #F5F3EE;
    margin-bottom: 4px;
  }

  .step span {
    font-size: 13px;
    color: #83807A;
    line-height: 1.5;
  }

  .card-wrap { position: relative; width: 100%; }

  .card-border-glow {
    position: absolute;
    inset: -1px;
    border-radius: 22px;
    background: linear-gradient(135deg, rgba(37,161,148,0.55), rgba(124,92,255,0.25), rgba(37,161,148,0.1));
    z-index: 0;
  }

  .card {
    position: relative;
    z-index: 1;
    background: rgba(17, 20, 19, 0.88);
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    border-radius: 21px;
    padding: 36px 34px 28px;
    box-shadow: 0 30px 70px rgba(0,0,0,0.55);
  }

  .card-head {
    text-align: center;
    margin-bottom: 28px;
  }

  .logo {
    width: 72px; height: 72px;
    border-radius: 50%;
    background: linear-gradient(135deg, rgba(37,161,148,0.18), rgba(37,161,148,0.03));
    border: 1.5px solid #25A194;
    display: flex; align-items: center; justify-content: center;
    margin: 0 auto 16px;
    padding: 10px;
    overflow: hidden;
  }

  .logo img { width: 100%; height: 100%; object-fit: contain; }

  .card-head h2 {
    font-family: 'Sora', sans-serif;
    font-weight: 700;
    font-size: 22px;
    color: #F5F3EE;
    margin-bottom: 6px;
  }

  .card-head .sub {
    font-size: 13px;
    color: #83807A;
    line-height: 1.6;
  }

  .alert-box {
    background: rgba(220, 53, 69, 0.12);
    border: 1px solid rgba(220, 53, 69, 0.35);
    border-radius: 10px;
    padding: 12px 14px;
    margin-bottom: 20px;
    font-size: 13px;
    color: #ffb4bc;
  }

  .alert-box ul { margin: 0; padding-left: 18px; }

  .section-title {
    display: flex;
    align-items: center;
    gap: 10px;
    font-family: 'Sora', sans-serif;
    font-size: 13px;
    font-weight: 700;
    letter-spacing: 0.04em;
    text-transform: uppercase;
    color: #25A194;
    margin: 24px 0 16px;
    padding-bottom: 10px;
    border-bottom: 1px solid rgba(255,255,255,0.07);
  }

  .section-title:first-of-type { margin-top: 0; }

  .section-title i { font-size: 16px; opacity: 0.9; }

  .form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
  }

  .form-grid .full { grid-column: 1 / -1; }

  .field { text-align: left; }

  .field label {
    display: block;
    font-size: 11px;
    font-weight: 600;
    color: #B7B4AC;
    margin-bottom: 7px;
    letter-spacing: 0.03em;
    text-transform: uppercase;
  }

  .field label .req { color: #25A194; margin-left: 2px; }

  .input-wrap {
    display: flex;
    align-items: center;
    gap: 10px;
    background: rgba(255,255,255,0.03);
    border: 1px solid rgba(255,255,255,0.09);
    border-radius: 10px;
    padding: 11px 13px;
    transition: border-color 0.15s ease, background 0.15s ease;
  }

  .input-wrap:focus-within {
    border-color: #25A194;
    background: rgba(37,161,148,0.05);
  }

  .input-wrap i { color: #25A194; font-size: 16px; flex-shrink: 0; opacity: 0.85; }

  .input-wrap input,
  .input-wrap textarea {
    flex: 1;
    width: 100%;
    background: transparent;
    border: none;
    outline: none;
    color: #F5F3EE;
    font-size: 14px;
    font-family: 'Inter', sans-serif;
    resize: vertical;
    min-height: 44px;
  }

  .input-wrap textarea { min-height: 72px; padding-top: 2px; }

  .input-wrap input::placeholder,
  .input-wrap textarea::placeholder { color: #706D66; }

  .field-error {
    display: block;
    margin-top: 5px;
    font-size: 11.5px;
    color: #ff8a96;
  }

  .form-actions {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    margin-top: 28px;
    padding-top: 20px;
    border-top: 1px solid rgba(255,255,255,0.07);
    flex-wrap: wrap;
  }

  .back-link {
    color: #25A194;
    text-decoration: none;
    font-size: 13px;
    font-weight: 500;
    display: inline-flex;
    align-items: center;
    gap: 6px;
  }

  .back-link:hover { text-decoration: underline; color: #2dd4bf; }

  button.submit {
    min-width: 200px;
    height: 48px;
    padding: 0 24px;
    background: linear-gradient(135deg, #2DBFAE, #1C8078);
    color: #fff;
    border: none;
    border-radius: 11px;
    font-family: 'Sora', sans-serif;
    font-weight: 700;
    font-size: 14px;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    transition: transform 0.15s ease, box-shadow 0.15s ease;
  }

  button.submit:hover {
    transform: translateY(-1px);
    box-shadow: 0 10px 24px rgba(37,161,148,0.35);
  }

  .copyright {
    margin-top: 22px;
    text-align: center;
    font-size: 11px;
    color: #605D57;
  }

  @media (max-width: 980px) {
    .page {
      grid-template-columns: 1fr;
      padding: 32px 20px 48px;
      gap: 32px;
    }

    .hero { padding-top: 0; }

    .form-grid { grid-template-columns: 1fr; }
  }
</style>
</head>
<body>

<div class="grid-overlay"></div>
<div class="bg-glow-1"></div>
<div class="bg-glow-2"></div>

<div class="page">

  <div class="hero">
    <div class="eyebrow"><span class="pulse-dot"></span> Join EasySchool</div>

    <h1>Bring your school<br>onto <span class="accent">one platform.</span></h1>
    <p>Register today and get billing, attendance, payroll, parent portal, reports, and more — all scoped securely to your school.</p>

    <div class="steps">
      <div class="step">
        <div class="step-num">1</div>
        <div>
          <strong>Submit registration</strong>
          <span>Fill in your school details and administrator account.</span>
        </div>
      </div>
      <div class="step">
        <div class="step-num">2</div>
        <div>
          <strong>Await approval</strong>
          <span>The platform owner reviews and approves your application.</span>
        </div>
      </div>
      <div class="step">
        <div class="step-num">3</div>
        <div>
          <strong>Receive your school code</strong>
          <span>Sign in with your unique code and start managing your school.</span>
        </div>
      </div>
    </div>
  </div>

  <div class="card-wrap">
    <div class="card-border-glow"></div>
    <div class="card">
      <div class="card-head">
        <div class="logo">
          <img src="{{ asset('assets/images/logo-icon.png') }}" alt="EasySchool">
        </div>
        <h2>Register your school</h2>
        <p class="sub">Complete the form below. Your account will be activated after approval.</p>
      </div>

      @if ($errors->any())
        <div class="alert-box">
          <ul>
            @foreach ($errors->all() as $error)
              <li>{{ $error }}</li>
            @endforeach
          </ul>
        </div>
      @endif

      <form method="POST" action="{{ route('register-school.process') }}">
        @csrf

        <div class="section-title"><i class="ri-building-2-line"></i> School information</div>

        <div class="form-grid">
          <div class="field">
            <label>School name <span class="req">*</span></label>
            <div class="input-wrap">
              <i class="ri-school-line"></i>
              <input type="text" name="name" value="{{ old('name') }}" placeholder="Greenfield Academy" required>
            </div>
            @error('name')<span class="field-error">{{ $message }}</span>@enderror
          </div>

          <div class="field">
            <label>School email</label>
            <div class="input-wrap">
              <i class="ri-mail-line"></i>
              <input type="email" name="email" value="{{ old('email') }}" placeholder="info@school.edu.gh">
            </div>
            @error('email')<span class="field-error">{{ $message }}</span>@enderror
          </div>

          <div class="field">
            <label>Phone</label>
            <div class="input-wrap">
              <i class="ri-phone-line"></i>
              <input type="text" name="phone" value="{{ old('phone') }}" placeholder="024xxxxxxx">
            </div>
            @error('phone')<span class="field-error">{{ $message }}</span>@enderror
          </div>

          <div class="field">
            <label>Website</label>
            <div class="input-wrap">
              <i class="ri-global-line"></i>
              <input type="text" name="website" value="{{ old('website') }}" placeholder="www.yourschool.com">
            </div>
            @error('website')<span class="field-error">{{ $message }}</span>@enderror
          </div>

          <div class="field full">
            <label>Address</label>
            <div class="input-wrap" style="align-items: flex-start;">
              <i class="ri-map-pin-line" style="margin-top: 12px;"></i>
              <textarea name="address" placeholder="Street, city, region">{{ old('address') }}</textarea>
            </div>
            @error('address')<span class="field-error">{{ $message }}</span>@enderror
          </div>
        </div>

        <div class="section-title"><i class="ri-shield-user-line"></i> Administrator account</div>

        <div class="form-grid">
          <div class="field">
            <label>Admin full name <span class="req">*</span></label>
            <div class="input-wrap">
              <i class="ri-user-line"></i>
              <input type="text" name="admin_name" value="{{ old('admin_name') }}" placeholder="Jane Mensah" required>
            </div>
            @error('admin_name')<span class="field-error">{{ $message }}</span>@enderror
          </div>

          <div class="field">
            <label>Admin email <span class="req">*</span></label>
            <div class="input-wrap">
              <i class="ri-mail-send-line"></i>
              <input type="email" name="admin_email" value="{{ old('admin_email') }}" placeholder="admin@school.edu.gh" required>
            </div>
            @error('admin_email')<span class="field-error">{{ $message }}</span>@enderror
          </div>

          <div class="field">
            <label>Admin phone</label>
            <div class="input-wrap">
              <i class="ri-smartphone-line"></i>
              <input type="text" name="admin_phone" value="{{ old('admin_phone') }}" placeholder="024xxxxxxx">
            </div>
            @error('admin_phone')<span class="field-error">{{ $message }}</span>@enderror
          </div>

          <div class="field">
            <label>Password <span class="req">*</span></label>
            <div class="input-wrap">
              <i class="ri-lock-line"></i>
              <input type="password" name="admin_password" placeholder="Minimum 8 characters" required>
            </div>
            @error('admin_password')<span class="field-error">{{ $message }}</span>@enderror
          </div>

          <div class="field full">
            <label>Confirm password <span class="req">*</span></label>
            <div class="input-wrap">
              <i class="ri-lock-password-line"></i>
              <input type="password" name="admin_password_confirmation" placeholder="Re-enter password" required>
            </div>
          </div>
        </div>

        <div class="form-actions">
          <a href="{{ route('admin-login') }}" class="back-link">
            <i class="ri-arrow-left-line"></i> Back to sign in
          </a>
          <button type="submit" class="submit">
            Submit registration
            <i class="ri-arrow-right-line"></i>
          </button>
        </div>
      </form>

      <div class="copyright">© 2026 EasySchool. All rights reserved.</div>
    </div>
  </div>

</div>

</body>
</html>
