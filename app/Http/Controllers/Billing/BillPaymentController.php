<?php

namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use App\Models\BillPayment;
use App\Http\Controllers\Pos\PosSaleController;
use App\Models\BillPaymentTransaction;
use App\Models\SchoolSetting;
use App\Models\Student;
use App\Services\Billing\BillPaymentProcessor;
use App\Services\Billing\BillPaymentSmsService;
use App\Services\Billing\PaystackService;
use App\Services\Billing\StudentBillCreditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use RuntimeException;

class BillPaymentController extends Controller
{
    public function __construct(
        private PaystackService $paystackService,
        private StudentBillCreditService $creditService,
        private BillPaymentSmsService $billPaymentSmsService,
        private BillPaymentProcessor $paymentProcessor,
    ) {}

    public function cashier($id)
    {
        $student = Student::with(['schoolClass.category'])->findOrFail($id);
        $bills = $this->paymentProcessor->outstandingBillsForStudent($student);
        $totalOutstanding = $bills->sum('balance');

        $creditBalance = $this->creditService->creditBalance($student);

        if ($bills->isEmpty() && $creditBalance <= 0) {
            return redirect()->route('student-bills')
                ->with('message_error', $student->full_name.' has no outstanding bills.');
        }

        return view('billing.record-payment', [
            'student' => $student,
            'bills' => $bills,
            'totalOutstanding' => $totalOutstanding,
            'creditBalance' => $creditBalance,
            'netPayable' => max(round($totalOutstanding - $creditBalance, 2), 0),
            'printStatementUrl' => route('student-bill-print', $student->id),
            'paystackPublicKey' => $this->paystackService->publicKey(),
            'paystackConfigured' => $this->paystackService->isConfigured(),
            'hasOutstandingBills' => $bills->isNotEmpty(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'amount' => 'required|numeric|min:0',
            'credit_applied' => 'nullable|numeric|min:0',
            'payment_method' => 'required|in:Cash',
            'reference' => 'nullable|string|max:100',
            'paid_at' => 'required|date',
            'notes' => 'nullable|string|max:500',
            'allocations' => 'nullable|array',
            'allocations.*.student_bill_id' => 'required|exists:student_bills,id',
            'allocations.*.amount' => 'required|numeric|min:0.01',
        ]);

        $student = Student::findOrFail($validated['student_id']);
        $cashAmount = (float) $validated['amount'];
        $creditApplied = round((float) ($validated['credit_applied'] ?? 0), 2);

        if ($cashAmount <= 0 && $creditApplied <= 0) {
            return $this->errorResponse($request, 'Enter a payment amount or apply credit.', 422);
        }

        try {
            $payment = DB::transaction(function () use ($validated, $student, $cashAmount, $creditApplied) {
                $allocations = $this->paymentProcessor->resolveAllocations(
                    $student->id,
                    $cashAmount,
                    $creditApplied,
                    collect($validated['allocations'] ?? [])
                );

                return $this->paymentProcessor->finalizePayment(
                    student: $student,
                    cashAmount: $cashAmount,
                    creditApplied: $creditApplied,
                    paymentMethod: ($cashAmount <= 0 && $creditApplied > 0) ? 'Credit' : $validated['payment_method'],
                    paidAt: $validated['paid_at'],
                    allocations: $allocations,
                    reference: $validated['reference'] ?? null,
                    notes: $validated['notes'] ?? null,
                    createdBy: Auth::id(),
                );
            });
        } catch (InvalidArgumentException $e) {
            return $this->errorResponse($request, $e->getMessage(), 422);
        } catch (\Throwable $e) {
            report($e);

            return $this->errorResponse($request, 'Unable to record payment. Please try again.', 500);
        }

        $sms = $this->notifyParentBySms($payment);

        return $this->successResponse($request, $payment, 'Payment recorded successfully.', $sms);
    }

    public function initializePaystack(Request $request)
    {
        if (! $this->paystackService->isConfigured()) {
            return $this->errorResponse($request, 'Paystack is not configured.', 503);
        }

        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'amount' => 'required|numeric|min:0',
            'credit_applied' => 'nullable|numeric|min:0',
            'allocations' => 'nullable|array',
            'allocations.*.student_bill_id' => 'required|exists:student_bills,id',
            'allocations.*.amount' => 'required|numeric|min:0.01',
        ]);

        $student = Student::findOrFail($validated['student_id']);
        $cashAmount = (float) $validated['amount'];
        $creditApplied = round((float) ($validated['credit_applied'] ?? 0), 2);

        if ($cashAmount <= 0 && $creditApplied <= 0) {
            return $this->errorResponse($request, 'Enter a payment amount or apply credit.', 422);
        }

        try {
            $allocations = $this->paymentProcessor->resolveAllocations(
                $student->id,
                $cashAmount,
                $creditApplied,
                collect($validated['allocations'] ?? [])
            );
        } catch (InvalidArgumentException $e) {
            return $this->errorResponse($request, $e->getMessage(), 422);
        }

        $reference = $this->generatePaystackReference();
        $amountInPesewas = (int) round($cashAmount * 100);

        $transaction = BillPaymentTransaction::create([
            'student_id' => $student->id,
            'reference' => $reference,
            'amount' => $cashAmount,
            'credit_applied' => $creditApplied,
            'currency' => config('paystack.currency', 'GHS'),
            'status' => BillPaymentTransaction::STATUS_PENDING,
            'allocations' => $allocations->values()->all(),
            'created_by' => Auth::id(),
            'initiated_by' => 'cashier',
        ]);

        if ($cashAmount <= 0) {
            try {
                $payment = DB::transaction(fn () => $this->paymentProcessor->finalizePayment(
                    student: $student,
                    cashAmount: 0,
                    creditApplied: $creditApplied,
                    paymentMethod: 'Credit',
                    paidAt: now()->toDateTimeString(),
                    allocations: $allocations,
                    reference: $reference,
                    notes: 'Paid fully from credit balance.',
                    createdBy: Auth::id(),
                ));

                $transaction->update([
                    'status' => BillPaymentTransaction::STATUS_SUCCESS,
                    'bill_payment_id' => $payment->id,
                ]);

                $sms = $this->notifyParentBySms($payment);

                return response()->json([
                    'message' => 'Payment completed using credit balance.',
                    'payment_id' => $payment->id,
                    'receipt_no' => $payment->receipt_no,
                    'receipt_url' => route('bill-payment-receipt', $payment->id),
                    'statement_url' => route('student-bill-print', $payment->student_id),
                    'credit_balance' => round((float) $payment->student->credit_balance, 2),
                    'credit_generated' => round((float) $payment->credit_generated, 2),
                    'paid_with_credit_only' => true,
                    'sms' => $sms,
                ]);
            } catch (InvalidArgumentException $e) {
                return $this->errorResponse($request, $e->getMessage(), 422);
            }
        }

        try {
            $paystackData = $this->paystackService->initializeTransaction(
                email: $this->paystackCustomerEmail($student),
                amountInPesewas: $amountInPesewas,
                reference: $reference,
                metadata: [
                    'student_id' => $student->id,
                    'student_code' => $student->student_id,
                    'transaction_id' => $transaction->id,
                ]
            );
        } catch (RuntimeException $e) {
            $transaction->update([
                'status' => BillPaymentTransaction::STATUS_FAILED,
                'gateway_response' => ['message' => $e->getMessage()],
            ]);

            return $this->errorResponse($request, $e->getMessage(), 422);
        }

        return response()->json([
            'message' => 'Paystack transaction initialized.',
            'reference' => $reference,
            'access_code' => $paystackData['access_code'] ?? null,
            'authorization_url' => $paystackData['authorization_url'] ?? null,
            'public_key' => $this->paystackService->publicKey(),
            'email' => $this->paystackCustomerEmail($student),
            'label' => $student->full_name,
            'amount' => $amountInPesewas,
            'currency' => config('paystack.currency', 'GHS'),
        ]);
    }

    public function verifyPaystack(Request $request)
    {
        $validated = $request->validate([
            'reference' => 'required|string|max:100',
        ]);

        try {
            $payment = $this->completePaystackTransaction($validated['reference']);
        } catch (InvalidArgumentException $e) {
            return $this->errorResponse($request, $e->getMessage(), 422);
        } catch (RuntimeException $e) {
            return $this->errorResponse($request, $e->getMessage(), 422);
        }

        $sms = $this->notifyParentBySms($payment);

        return $this->successResponse($request, $payment, 'Paystack payment verified successfully.', $sms);
    }

    public function webhook(Request $request)
    {
        $payload = $request->getContent();
        $signature = $request->header('x-paystack-signature');

        if (! $this->paystackService->verifyWebhookSignature($payload, $signature)) {
            return response()->json(['message' => 'Invalid signature.'], 401);
        }

        $event = json_decode($payload, true);

        if (($event['event'] ?? null) !== 'charge.success') {
            return response()->json(['message' => 'Ignored.']);
        }

        $reference = data_get($event, 'data.reference');

        if (! $reference) {
            return response()->json(['message' => 'Missing reference.'], 422);
        }

        if (str_starts_with((string) $reference, 'SUB-')) {
            try {
                app(\App\Http\Controllers\Subscription\SchoolSubscriptionPaymentController::class)
                    ->completePaystackFromWebhook((string) $reference);
            } catch (InvalidArgumentException|RuntimeException) {
                return response()->json(['message' => 'Unable to process webhook.'], 422);
            }

            return response()->json(['message' => 'Webhook processed.']);
        }

        try {
            $this->completePaystackTransaction($reference);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            try {
                app(PosSaleController::class)->completePaystackFromWebhook($reference);
            } catch (InvalidArgumentException|RuntimeException|\Illuminate\Database\Eloquent\ModelNotFoundException) {
                try {
                    app(\App\Http\Controllers\Subscription\SchoolSubscriptionPaymentController::class)
                        ->completePaystackFromWebhook((string) $reference);
                } catch (InvalidArgumentException|RuntimeException) {
                    return response()->json(['message' => 'Unable to process webhook.'], 422);
                }
            }
        } catch (InvalidArgumentException|RuntimeException) {
            return response()->json(['message' => 'Unable to process webhook.'], 422);
        }

        return response()->json(['message' => 'Webhook processed.']);
    }

    public function receipt($id)
    {
        $payment = BillPayment::with([
            'student.schoolClass.category',
            'allocations.studentBill.billingItem',
        ])->findOrFail($id);

        $payment->student->refresh();

        return view('billing.payment-receipt', [
            'payment' => $payment,
            'school' => SchoolSetting::current(),
            'statementUrl' => route('student-bill-print', $payment->student_id),
            'backUrl' => route('student-bills'),
            'backLabel' => 'Back to Student Bills',
        ]);
    }

    private function completePaystackTransaction(string $reference): BillPayment
    {
        $transaction = BillPaymentTransaction::query()
            ->where('reference', $reference)
            ->firstOrFail();

        if ($transaction->status === BillPaymentTransaction::STATUS_SUCCESS && $transaction->bill_payment_id) {
            return BillPayment::with(['student', 'allocations.studentBill.billingItem'])
                ->findOrFail($transaction->bill_payment_id);
        }

        $paystackData = $this->paystackService->verifyTransaction($reference);

        if (($paystackData['status'] ?? null) !== 'success') {
            $transaction->update([
                'status' => BillPaymentTransaction::STATUS_FAILED,
                'gateway_response' => $paystackData,
            ]);

            throw new InvalidArgumentException('Paystack payment was not successful.');
        }

        $verifiedAmount = round(((int) ($paystackData['amount'] ?? 0)) / 100, 2);

        if (abs($verifiedAmount - (float) $transaction->amount) > 0.009) {
            throw new InvalidArgumentException('Verified payment amount does not match the initiated transaction.');
        }

        return DB::transaction(function () use ($transaction, $paystackData, $verifiedAmount) {
            $lockedTransaction = BillPaymentTransaction::query()
                ->whereKey($transaction->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedTransaction->status === BillPaymentTransaction::STATUS_SUCCESS && $lockedTransaction->bill_payment_id) {
                return BillPayment::with(['student', 'allocations.studentBill.billingItem'])
                    ->findOrFail($lockedTransaction->bill_payment_id);
            }

            $student = Student::findOrFail($lockedTransaction->student_id);
            $allocations = collect($lockedTransaction->allocations ?? []);
            $creditApplied = round((float) $lockedTransaction->credit_applied, 2);

            $payment = $this->paymentProcessor->finalizePayment(
                student: $student,
                cashAmount: $verifiedAmount,
                creditApplied: $creditApplied,
                paymentMethod: 'Paystack',
                paidAt: data_get($paystackData, 'paid_at', now()->toDateTimeString()),
                allocations: $allocations,
                reference: $lockedTransaction->reference,
                notes: null,
                paymentChannel: $paystackData['channel'] ?? null,
                gatewayTransactionId: isset($paystackData['id']) ? (string) $paystackData['id'] : null,
                createdBy: $lockedTransaction->created_by,
            );

            $lockedTransaction->update([
                'status' => BillPaymentTransaction::STATUS_SUCCESS,
                'paystack_transaction_id' => isset($paystackData['id']) ? (string) $paystackData['id'] : null,
                'paystack_channel' => $paystackData['channel'] ?? null,
                'gateway_response' => $paystackData,
                'bill_payment_id' => $payment->id,
            ]);

            $this->notifyParentBySms($payment);

            return $payment;
        });
    }

    private function notifyParentBySms(BillPayment $payment): array
    {
        try {
            return $this->billPaymentSmsService->sendPaymentConfirmation($payment);
        } catch (\Throwable $e) {
            report($e);

            return [
                'sent' => false,
                'phone' => null,
                'message' => 'Unable to send SMS notification.',
            ];
        }
    }

    private function generatePaystackReference(): string
    {
        $year = now()->format('Y');
        $prefix = \App\Support\TenantCodePrefix::segment().'BILL-' . $year . '-';
        $last = BillPaymentTransaction::query()
            ->where('reference', 'like', $prefix . '%')
            ->orderByDesc('id')
            ->value('reference');

        $sequence = 1;

        if ($last && preg_match('/-(\d+)$/', $last, $matches)) {
            $sequence = ((int) $matches[1]) + 1;
        }

        return $prefix . str_pad((string) $sequence, 5, '0', STR_PAD_LEFT);
    }

    private function paystackCustomerEmail(Student $student): string
    {
        $phone = preg_replace('/\D/', '', (string) $student->phone);

        if ($phone === '') {
            $phone = 'student'.$student->id;
        }

        return $phone.'@'.$this->paystackPlaceholderDomain();
    }

    private function paystackPlaceholderDomain(): string
    {
        $configured = trim((string) config('paystack.placeholder_domain', ''));

        if ($configured !== '') {
            return $configured;
        }

        $school = SchoolSetting::current();
        $schoolEmail = trim((string) ($school->email ?? ''));

        if ($schoolEmail !== '' && filter_var($schoolEmail, FILTER_VALIDATE_EMAIL)) {
            return substr(strrchr($schoolEmail, '@'), 1);
        }

        $websiteHost = parse_url((string) ($school->website ?? ''), PHP_URL_HOST);

        if (is_string($websiteHost) && $websiteHost !== '' && ! in_array($websiteHost, ['localhost', '127.0.0.1'], true)) {
            return preg_replace('/^www\./', '', $websiteHost);
        }

        return 'easyschool.com';
    }

    private function successResponse(Request $request, BillPayment $payment, string $message, ?array $sms = null)
    {
        if ($request->expectsJson()) {
            $payment->loadMissing('student');

            return response()->json([
                'message' => $message,
                'payment_id' => $payment->id,
                'receipt_no' => $payment->receipt_no,
                'receipt_url' => route('bill-payment-receipt', $payment->id),
                'statement_url' => route('student-bill-print', $payment->student_id),
                'credit_balance' => round((float) $payment->student->credit_balance, 2),
                'credit_generated' => round((float) $payment->credit_generated, 2),
                'sms' => $sms,
            ]);
        }

        $redirect = redirect()->route('bill-payment-receipt', $payment->id)
            ->with('message_success', $message);

        if ($sms) {
            $redirect->with('payment_sms_status', $sms);
        }

        return $redirect;
    }

    private function errorResponse(Request $request, string $message, int $status = 422)
    {
        if ($request->expectsJson()) {
            return response()->json(['message' => $message], $status);
        }

        return back()->with('message_error', $message);
    }
}
