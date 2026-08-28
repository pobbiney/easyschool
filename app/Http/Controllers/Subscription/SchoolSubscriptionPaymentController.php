<?php

namespace App\Http\Controllers\Subscription;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Models\SchoolSubscriptionPayment;
use App\Services\Billing\PaystackService;
use App\Services\Subscription\SchoolSubscriptionService;
use App\Support\GhanaPhone;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;
use RuntimeException;

class SchoolSubscriptionPaymentController extends Controller
{
    public function __construct(
        private SchoolSubscriptionService $subscriptions,
        private PaystackService $paystack,
    ) {}

    public function show(Request $request)
    {
        $plan = $this->subscriptions->plan();
        $prefillCode = strtoupper(trim((string) $request->query('school_code', '')));

        return view('subscription.renew', [
            'plan' => $plan,
            'paystackConfigured' => $this->paystack->isConfigured(),
            'prefillCode' => $prefillCode,
        ]);
    }

    public function lookupSchool(Request $request): JsonResponse
    {
        $code = strtoupper(trim((string) $request->query('school_code', '')));

        if ($code === '') {
            return response()->json(['message' => 'Enter a school code.'], 422);
        }

        $school = $this->resolvePayableSchool($code);

        if (! $school) {
            return response()->json(['message' => 'School code does not match our records.'], 404);
        }

        if (! $this->subscriptions->schoolCanPay($school)) {
            return response()->json(['message' => 'This school cannot renew from this page. Contact support.'], 422);
        }

        return response()->json([
            'school' => $this->schoolPayload($school),
        ]);
    }

    public function initializePaystack(Request $request): JsonResponse
    {
        if (! $this->paystack->isConfigured()) {
            return response()->json(['message' => 'Paystack is not configured.'], 503);
        }

        $plan = $this->subscriptions->plan();

        if (! $plan) {
            return response()->json(['message' => 'No subscription plan has been set up.'], 422);
        }

        $validated = $request->validate([
            'school_code' => 'required|string|max:32',
            'payer_full_name' => 'required|string|max:255',
            'payer_phone' => 'required|string|max:30',
            'payer_email' => 'required|email|max:255',
        ]);

        $phone = GhanaPhone::normalize($validated['payer_phone']);

        if (! $phone) {
            return response()->json(['message' => 'Enter a valid Ghana phone number (e.g. 024xxxxxxx).'], 422);
        }

        $school = $this->resolvePayableSchool($validated['school_code']);

        if (! $school) {
            return response()->json(['message' => 'School code does not match our records.'], 422);
        }

        if (! $this->subscriptions->schoolCanPay($school)) {
            return response()->json(['message' => 'This school cannot renew from this page. Contact support.'], 422);
        }

        $amount = round((float) $plan->amount, 2);
        $reference = $this->subscriptions->nextReference();

        $payment = SchoolSubscriptionPayment::query()->create([
            'school_id' => $school->id,
            'subscription_id' => $plan->id,
            'amount' => $amount,
            'payer_full_name' => trim($validated['payer_full_name']),
            'payer_phone' => $phone,
            'payer_email' => strtolower(trim($validated['payer_email'])),
            'paystack_reference' => $reference,
            'status' => SchoolSubscriptionPayment::STATUS_PENDING,
        ]);

        try {
            $paystackData = $this->paystack->initializeTransaction(
                email: $payment->payer_email,
                amountInPesewas: (int) round($amount * 100),
                reference: $reference,
                metadata: [
                    'type' => 'school_subscription',
                    'school_id' => $school->id,
                    'school_code' => $school->code,
                    'payment_id' => $payment->id,
                ],
            );
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json([
            'message' => 'Paystack checkout ready.',
            'reference' => $reference,
            'public_key' => $this->paystack->publicKey(),
            'email' => $payment->payer_email,
            'amount' => (int) round($amount * 100),
            'currency' => config('paystack.currency', 'GHS'),
            'label' => $school->displayLabel(),
            'access_code' => $paystackData['access_code'] ?? null,
            'authorization_url' => $paystackData['authorization_url'] ?? null,
        ]);
    }

    public function verifyPaystack(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'reference' => 'required|string|max:64',
        ]);

        try {
            $payment = $this->completeVerifiedPayment($validated['reference']);
        } catch (InvalidArgumentException|RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json([
            'message' => 'Payment confirmed. Enter the reference sent to your phone to activate the school.',
            'reference' => $payment->paystack_reference,
            'sms_sent' => (bool) $payment->sms_sent_at,
            'activate_url' => route('renew-subscription.activate', ['reference' => $payment->paystack_reference]),
        ]);
    }

    public function completePaystackFromWebhook(string $reference): SchoolSubscriptionPayment
    {
        return $this->completeVerifiedPayment($reference);
    }

    public function showActivate(Request $request)
    {
        return view('subscription.activate', [
            'reference' => strtoupper(trim((string) $request->query('reference', ''))),
        ]);
    }

    public function activate(Request $request)
    {
        $validated = $request->validate([
            'reference' => 'required|string|max:64',
        ]);

        try {
            $school = $this->subscriptions->activateByReference($validated['reference']);
        } catch (InvalidArgumentException|RuntimeException $e) {
            return back()
                ->withInput()
                ->with('message_error', $e->getMessage());
        }

        return redirect()
            ->route('admin-login')
            ->with('login_success_message', $school->name.' is active again. Sign in with your school code, email, and password.');
    }

    private function completeVerifiedPayment(string $reference): SchoolSubscriptionPayment
    {
        $reference = strtoupper(trim($reference));

        $payment = SchoolSubscriptionPayment::query()
            ->where('paystack_reference', $reference)
            ->first();

        if (! $payment) {
            throw new InvalidArgumentException('Subscription payment was not found for this reference.');
        }

        if ($payment->isPaid() || $payment->isActivated()) {
            $this->subscriptions->notifyPayer($payment);

            return $payment->fresh();
        }

        $paystackData = $this->paystack->verifyTransaction($reference);

        if (($paystackData['status'] ?? null) !== 'success') {
            throw new InvalidArgumentException('Paystack payment was not successful.');
        }

        $verifiedAmount = round(((int) ($paystackData['amount'] ?? 0)) / 100, 2);

        if (abs($verifiedAmount - (float) $payment->amount) > 0.009) {
            throw new InvalidArgumentException('Paid amount does not match the subscription plan.');
        }

        return $this->subscriptions->markPaid($payment, $paystackData);
    }

    private function resolvePayableSchool(string $code): ?School
    {
        return School::query()
            ->where('code', strtoupper(trim($code)))
            ->first();
    }

    /**
     * @return array{name: string, code: string, address: ?string, phone: ?string, email: ?string}
     */
    private function schoolPayload(School $school): array
    {
        return [
            'name' => $school->name,
            'code' => $school->code,
            'address' => $school->address,
            'phone' => $school->phone,
            'email' => $school->email,
        ];
    }
}
