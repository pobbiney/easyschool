<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Activate Subscription — EasySchool</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Sora:wght@600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet">
<style>
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body { font-family: 'Inter', sans-serif; color: #ECEAE4; background: #0A0D0C; min-height: 100vh; }
  .page { max-width: 480px; margin: 0 auto; padding: 64px 20px; }
  .card { background: rgba(17, 20, 19, 0.88); border: 1px solid rgba(37,161,148,0.28); border-radius: 21px; padding: 36px 28px 28px; }
  h1 { font-family: 'Sora', sans-serif; font-size: 24px; margin-bottom: 8px; color: #F5F3EE; }
  .sub { color: #A6A39B; font-size: 14px; line-height: 1.6; margin-bottom: 22px; }
  label { display: block; font-size: 11px; font-weight: 600; letter-spacing: 0.04em; text-transform: uppercase; color: #B7B4AC; margin-bottom: 7px; }
  .req { color: #25A194; }
  input { width: 100%; background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.09); border-radius: 10px; padding: 12px 14px; color: #F5F3EE; font-size: 14px; font-family: inherit; margin-bottom: 16px; }
  input:focus { outline: none; border-color: #25A194; }
  .err { background: rgba(220,53,69,0.12); border: 1px solid rgba(220,53,69,0.35); color: #ffb4bc; padding: 10px 12px; border-radius: 10px; margin-bottom: 14px; font-size: 13px; }
  .submit { width: 100%; height: 48px; border: none; border-radius: 11px; background: linear-gradient(135deg, #2DBFAE, #1C8078); color: #fff; font-family: 'Sora', sans-serif; font-weight: 700; cursor: pointer; }
  .back { display: inline-block; margin-top: 18px; color: #25A194; font-size: 13px; text-decoration: none; }
</style>
</head>
<body>
<div class="page">
  <div class="card">
    <h1>Activate school</h1>
    <p class="sub">Enter the reference number sent to the payer’s phone after Paystack payment.</p>

    @if(session('message_error'))
      <div class="err">{{ session('message_error') }}</div>
    @endif
    @error('reference')
      <div class="err">{{ $message }}</div>
    @enderror

    <form method="POST" action="{{ route('renew-subscription.activate.process') }}">
      @csrf
      <label>Reference number <span class="req">*</span></label>
      <input type="text" name="reference" value="{{ old('reference', $reference) }}" required placeholder="e.g. SUB-2026-00001">
      <button type="submit" class="submit">Activate school account</button>
    </form>
    <a class="back" href="{{ route('renew-subscription') }}">Pay again</a>
    &nbsp;·&nbsp;
    <a class="back" href="{{ route('admin-login') }}">Sign in</a>
  </div>
</div>
</body>
</html>
