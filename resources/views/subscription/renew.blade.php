<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Renew Subscription — EasySchool</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Sora:wght@600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet">
@if($paystackConfigured)
<script src="https://js.paystack.co/v1/inline.js"></script>
@endif
<style>
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body { font-family: 'Inter', sans-serif; color: #ECEAE4; background: #0A0D0C; min-height: 100vh; }
  .page { position: relative; z-index: 1; max-width: 560px; margin: 0 auto; padding: 48px 20px 64px; }
  .card { background: rgba(17, 20, 19, 0.88); border: 1px solid rgba(37,161,148,0.28); border-radius: 21px; padding: 32px 28px 28px; }
  h1 { font-family: 'Sora', sans-serif; font-size: 24px; margin-bottom: 8px; color: #F5F3EE; }
  .sub { color: #A6A39B; font-size: 14px; line-height: 1.6; margin-bottom: 22px; }
  .amount { color: #25A194; font-weight: 700; font-size: 18px; margin-bottom: 20px; }
  .field { margin-bottom: 16px; }
  label { display: block; font-size: 11px; font-weight: 600; letter-spacing: 0.04em; text-transform: uppercase; color: #B7B4AC; margin-bottom: 7px; }
  .req { color: #25A194; }
  input { width: 100%; background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.09); border-radius: 10px; padding: 12px 14px; color: #F5F3EE; font-size: 14px; font-family: inherit; }
  input:focus { outline: none; border-color: #25A194; }
  .school-box { display: none; background: rgba(37,161,148,0.08); border: 1px solid rgba(37,161,148,0.25); border-radius: 12px; padding: 14px 16px; margin-bottom: 18px; font-size: 13px; line-height: 1.6; color: #C8C5BD; }
  .school-box strong { color: #F5F3EE; display: block; font-size: 15px; margin-bottom: 4px; }
  .msg { display: none; margin-bottom: 14px; font-size: 13px; padding: 10px 12px; border-radius: 10px; }
  .msg.show { display: block; }
  .msg.err { background: rgba(220,53,69,0.12); border: 1px solid rgba(220,53,69,0.35); color: #ffb4bc; }
  .msg.ok { background: rgba(37,161,148,0.12); border: 1px solid rgba(37,161,148,0.35); color: #5eead4; }
  .submit { width: 100%; height: 48px; border: none; border-radius: 11px; background: linear-gradient(135deg, #2DBFAE, #1C8078); color: #fff; font-family: 'Sora', sans-serif; font-weight: 700; cursor: pointer; }
  .submit:disabled { opacity: 0.6; cursor: not-allowed; }
  .back { display: inline-block; margin-top: 18px; color: #25A194; font-size: 13px; text-decoration: none; }
</style>
</head>
<body>
<div class="page">
  <div class="card">
    <h1>Renew subscription</h1>
    <p class="sub">Pay for the current plan, then enter the SMS reference to activate the school account.</p>

    @if($plan)
      <div class="amount">{{ $plan->name }} — {{ \App\Support\Money::ghs($plan->amount) }}</div>
    @else
      <p class="msg show err">No subscription plan has been set up. Ask the platform administrator.</p>
    @endif

    <div id="msg" class="msg"></div>
    <div id="schoolBox" class="school-box"></div>

    <form id="renewForm">
      <div class="field">
        <label>School code <span class="req">*</span></label>
        <input type="text" name="school_code" id="school_code" value="{{ $prefillCode }}" required placeholder="e.g. SCH-2026-A7K9">
      </div>
      <div class="field">
        <label>Payer's full name <span class="req">*</span></label>
        <input type="text" name="payer_full_name" id="payer_full_name" required placeholder="Jane Mensah">
      </div>
      <div class="field">
        <label>Payer's phone number <span class="req">*</span></label>
        <input type="text" name="payer_phone" id="payer_phone" required placeholder="024xxxxxxx">
      </div>
      <div class="field">
        <label>Email <span class="req">*</span></label>
        <input type="email" name="payer_email" id="payer_email" required placeholder="you@example.com">
      </div>
      <button type="submit" class="submit" id="payBtn" {{ ($plan && $paystackConfigured) ? '' : 'disabled' }}>
        {{ $paystackConfigured ? 'Pay with Paystack' : 'Paystack is not configured' }}
      </button>
    </form>
    <a class="back" href="{{ route('admin-login') }}"><i class="ri-arrow-left-line"></i> Back to sign in</a>
  </div>
</div>
<script>
(function () {
  const csrf = @json(csrf_token());
  const lookupUrl = @json(route('renew-subscription.school'));
  const initUrl = @json(route('renew-subscription.paystack.initialize'));
  const verifyUrl = @json(route('renew-subscription.paystack.verify'));
  const paystackConfigured = @json((bool) $paystackConfigured);
  const codeInput = document.getElementById('school_code');
  const schoolBox = document.getElementById('schoolBox');
  const msg = document.getElementById('msg');
  const btn = document.getElementById('payBtn');
  const form = document.getElementById('renewForm');

  function showMsg(text, ok) {
    msg.textContent = text;
    msg.className = 'msg show ' + (ok ? 'ok' : 'err');
  }

  async function lookup() {
    const code = (codeInput.value || '').trim();
    if (!code) { schoolBox.style.display = 'none'; return; }
    try {
      const res = await fetch(lookupUrl + '?school_code=' + encodeURIComponent(code), { headers: { 'Accept': 'application/json' } });
      const data = await res.json().catch(() => ({}));
      if (!res.ok) {
        schoolBox.style.display = 'none';
        showMsg(data.message || 'School code does not match our records.', false);
        return;
      }
      msg.className = 'msg';
      const s = data.school || {};
      schoolBox.innerHTML = '<strong>' + (s.name || '') + '</strong>' +
        'Code: ' + (s.code || '') +
        (s.address ? '<br>' + s.address : '') +
        (s.phone ? '<br>' + s.phone : '') +
        (s.email ? '<br>' + s.email : '');
      schoolBox.style.display = 'block';
    } catch (e) {
      showMsg('Could not look up that school code.', false);
    }
  }

  codeInput.addEventListener('blur', lookup);
  if ((codeInput.value || '').trim()) lookup();

  form.addEventListener('submit', async function (e) {
    e.preventDefault();
    if (!paystackConfigured || typeof PaystackPop === 'undefined') {
      showMsg('Online payment is not available yet.', false);
      return;
    }
    btn.disabled = true;
    showMsg('Opening payment…', true);
    try {
      const res = await fetch(initUrl, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
        body: JSON.stringify({
          school_code: codeInput.value,
          payer_full_name: document.getElementById('payer_full_name').value,
          payer_phone: document.getElementById('payer_phone').value,
          payer_email: document.getElementById('payer_email').value,
        }),
      });
      const data = await res.json().catch(() => ({}));
      if (!res.ok) throw new Error(data.message || 'Unable to start payment.');

      PaystackPop.setup({
        key: data.public_key,
        email: data.email,
        amount: data.amount,
        currency: data.currency || 'GHS',
        ref: data.reference,
        label: data.label,
        callback(response) {
          showMsg('Verifying payment…', true);
          fetch(verifyUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
            body: JSON.stringify({ reference: response.reference }),
          }).then(function (vr) { return vr.json().then(function (body) { return { ok: vr.ok, body: body }; }); })
            .then(function (result) {
              if (!result.ok) throw new Error(result.body.message || 'Verification failed.');
              window.location.href = result.body.activate_url;
            }).catch(function (err) {
              showMsg(err.message || 'Verification failed.', false);
              btn.disabled = false;
            });
        },
        onClose() {
          btn.disabled = false;
          msg.className = 'msg';
        },
      }).openIframe();
    } catch (err) {
      showMsg(err.message || 'Payment failed.', false);
      btn.disabled = false;
    }
  });
})();
</script>
</body>
</html>
