<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Registration Submitted — EasySchool</title>
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
    justify-content: center;
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
    background: radial-gradient(circle, rgba(37,161,148,0.16) 0%, transparent 70%);
    top: -220px; left: -180px;
    z-index: 0;
  }

  .bg-glow-2 {
    position: absolute;
    width: 620px; height: 620px;
    background: radial-gradient(circle, rgba(124,92,255,0.09) 0%, transparent 70%);
    bottom: -260px; right: 6%;
    z-index: 0;
  }

  .card-wrap {
    position: relative;
    z-index: 1;
    width: 100%;
    max-width: 480px;
    margin: 24px;
  }

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
    padding: 48px 38px 36px;
    box-shadow: 0 30px 70px rgba(0,0,0,0.55);
    text-align: center;
  }

  .success-icon {
    width: 80px; height: 80px;
    border-radius: 50%;
    background: rgba(37,161,148,0.15);
    border: 1.5px solid rgba(37,161,148,0.45);
    display: flex; align-items: center; justify-content: center;
    margin: 0 auto 22px;
    font-size: 36px;
    color: #25A194;
  }

  .card h1 {
    font-family: 'Sora', sans-serif;
    font-weight: 700;
    font-size: 24px;
    color: #F5F3EE;
    margin-bottom: 12px;
  }

  .card p {
    font-size: 14px;
    line-height: 1.7;
    color: #A6A39B;
    margin-bottom: 10px;
  }

  .school-name {
    color: #25A194;
    font-weight: 600;
  }

  .submit {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    margin-top: 24px;
    min-width: 220px;
    height: 48px;
    padding: 0 24px;
    background: linear-gradient(135deg, #2DBFAE, #1C8078);
    color: #fff;
    border: none;
    border-radius: 11px;
    font-family: 'Sora', sans-serif;
    font-weight: 700;
    font-size: 14px;
    text-decoration: none;
    transition: transform 0.15s ease, box-shadow 0.15s ease;
  }

  .submit:hover {
    transform: translateY(-1px);
    box-shadow: 0 10px 24px rgba(37,161,148,0.35);
    color: #fff;
  }

  .copyright {
    margin-top: 28px;
    font-size: 11px;
    color: #605D57;
  }
</style>
</head>
<body>

<div class="grid-overlay"></div>
<div class="bg-glow-1"></div>
<div class="bg-glow-2"></div>

<div class="card-wrap">
  <div class="card-border-glow"></div>
  <div class="card">
    <div class="success-icon"><i class="ri-checkbox-circle-fill"></i></div>
    <h1>Registration submitted</h1>
    <p>Thank you, <span class="school-name">{{ $school->name }}</span>.</p>
    <p>Your application is pending approval by the system administrator.</p>
    <p>Once approved, you will receive your unique school code and can sign in as school administrator.</p>
    @if(!empty($smsSent))
      <p>We have sent an SMS to confirm we received your registration. You will be notified when it is approved.</p>
    @elseif($school->admin_phone || $school->phone)
      <p>You will be notified by SMS when your registration is approved.</p>
    @endif
    <a href="{{ route('admin-login') }}" class="submit">
      Return to sign in
      <i class="ri-arrow-right-line"></i>
    </a>
    <div class="copyright">© 2026 EasySchool. All rights reserved.</div>
  </div>
</div>

</body>
</html>
