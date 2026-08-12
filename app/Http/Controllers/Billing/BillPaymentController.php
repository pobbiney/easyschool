<?php

namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use App\Models\BillPayment;
use App\Models\BillPaymentAllocation;
use App\Models\BillPaymentTransaction;
use App\Models\SchoolSetting;
use App\Models\Student;
use App\Models\StudentBill;
use App\Models\StudentBillCreditTransaction;
use App\Services\Billing\PaystackService;
use App\Services\Billing\StudentBillCreditService;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use RuntimeException;

class BillPaymentController extends Controller
{
    public function __construct(
        private PaystackService $paystackService,
        private StudentBillCreditService $creditService,
    ) {}

    public function cashier($id)
    {
        $student = Student::with(['schoolClass.category'])->findOrFail($id);
        $bills = $this->outstandingBillsForStudent($student);
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
                $allocations = $this->resolveAllocations(
                    $student->id,
                    $cashAmount,
                    $creditApplied,
                    collect($validated['allocations'] ?? [])
                );

                return $this->finalizePayment(
                    student: $student,
                    cashAmount: $cashAmount,
                    creditApplied: $creditApplied,
                    paymentMethod: ($cashAmount <= 0 && $creditApplied > 0) ? 'Credit' : $validated['payment_method'],
                    paidAt: $validated['paid_at'],
                    allocations: $allocations,
                    reference: $validated['reference'] ?? null,
                    notes: $validated['notes'] ?? null,
                );
            });
        } catch (InvalidArgumentException $e) {
            return $this->errorResponse($request, $e->getMessage(), 422);
        } catch (\Throwable $e) {
            report($e);

            return $this->errorResponse($request, 'Unable to record payment. Please try again.', 500);
        }

        return $this->successResponse($request, $payment, 'Payment recorded successfully.');
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
            $allocations = $this->resolveAllocations(
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
        ]);

        if ($cashAmount <= 0) {
            try {
                $payment = DB::transaction(fn () => $this->finalizePayment(
                    student: $student,
                    cashAmount: 0,
                    creditApplied: $creditApplied,
                    paymentMethod: 'Credit',
                    paidAt: now()->toDateTimeString(),
                    allocations: $allocations,
                    reference: $reference,
                    notes: 'Paid fully from credit balance.',
                ));

                $transaction->update([
                    'status' => BillPaymentTransaction::STATUS_SUCCESS,
                    'bill_payment_id' => $payment->id,
                ]);

                return response()->json([
                    'message' => 'Payment completed using credit balance.',
                    'payment_id' => $payment->id,
                    'receipt_no' => $payment->receipt_no,
                    'receipt_url' => route('bill-payment-receipt', $payment->id),
                    'statement_url' => route('student-bill-print', $payment->student_id),
                    'credit_balance' => round((float) $payment->student->credit_balance, 2),
                    'credit_generated' => round((float) $payment->credit_generated, 2),
                    'paid_with_credit_only' => true,
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

        return $this->successResponse($request, $payment, 'Paystack payment verified successfully.');
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

        try {
            $this->completePaystackTransaction($reference);
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

            $payment = $this->finalizePayment(
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
            );

            $lockedTransaction->update([
                'status' => BillPaymentTransaction::STATUS_SUCCESS,
                'paystack_transaction_id' => isset($paystackData['id']) ? (string) $paystackData['id'] : null,
                'paystack_channel' => $paystackData['channel'] ?? null,
                'gateway_response' => $paystackData,
                'bill_payment_id' => $payment->id,
            ]);

            return $payment;
        });
    }

    private function finalizePayment(
        Student $student,
        float $cashAmount,
        float $creditApplied,
        string $paymentMethod,
        string $paidAt,
        Collection $allocations,
        ?string $reference = null,
        ?string $notes = null,
        ?string $paymentChannel = null,
        ?string $gatewayTransactionId = null,
    ): BillPayment {
        $cashAmount = round($cashAmount, 2);
        $creditApplied = round($creditApplied, 2);
        $allocatedTotal = round($allocations->sum('amount'), 2);
        $totalFunding = round($cashAmount + $creditApplied, 2);

        if ($allocatedTotal <= 0) {
            throw new InvalidArgumentException('No outstanding bills to allocate this payment to.');
        }

        if ($allocatedTotal > $totalFunding + 0.009) {
            throw new InvalidArgumentException('Allocated amount exceeds available payment and credit.');
        }

        if ($creditApplied > $this->creditService->creditBalance($student) + 0.009) {
            throw new InvalidArgumentException('Insufficient credit balance.');
        }

        $payment = BillPayment::create([
            'student_id' => $student->id,
            'receipt_no' => $this->generateReceiptNumber(),
            'amount' => $cashAmount,
            'credit_applied' => $creditApplied,
            'credit_generated' => 0,
            'payment_method' => $paymentMethod,
            'reference' => $reference,
            'payment_channel' => $paymentChannel,
            'gateway_transaction_id' => $gatewayTransactionId,
            'paid_at' => $paidAt,
            'notes' => $notes,
            'created_by' => Auth::id(),
        ]);

        if ($creditApplied > 0) {
            $this->creditService->applyCredit(
                $student,
                $creditApplied,
                $payment->id,
                'Applied to bill payment '.$payment->receipt_no
            );
        }

        foreach ($allocations as $allocation) {
            $bill = StudentBill::query()
                ->where('id', $allocation['student_bill_id'])
                ->where('student_id', $student->id)
                ->lockForUpdate()
                ->firstOrFail();

            $amount = min((float) $allocation['amount'], (float) $bill->balance);

            if ($amount <= 0) {
                continue;
            }

            BillPaymentAllocation::create([
                'bill_payment_id' => $payment->id,
                'student_bill_id' => $bill->id,
                'amount' => $amount,
            ]);

            $bill->amount_paid = round((float) $bill->amount_paid + $amount, 2);
            $bill->refreshTotals();
            $bill->save();
        }

        $appliedTotal = round($payment->allocations()->sum('amount'), 2);
        $surplus = round($totalFunding - $appliedTotal, 2);

        if ($surplus > 0.009) {
            $this->creditService->addCredit(
                $student,
                $surplus,
                StudentBillCreditTransaction::SOURCE_OVERPAYMENT,
                $payment->id,
                'Overpayment saved from '.$payment->receipt_no
            );

            $payment->credit_generated = $surplus;
            $payment->save();
        }

        return $payment->load(['student', 'allocations.studentBill.billingItem']);
    }

    private function resolveAllocations(
        int $studentId,
        float $cashAmount,
        float $creditApplied,
        Collection $allocations,
    ): Collection {
        $totalFunding = round($cashAmount + $creditApplied, 2);

        if ($allocations->isEmpty()) {
            $allocations = $this->buildAutoAllocations($studentId, $totalFunding);
        }

        $allocatedTotal = round($allocations->sum('amount'), 2);

        if ($allocatedTotal <= 0) {
            throw new InvalidArgumentException('No outstanding bills to allocate this payment to.');
        }

        if ($allocatedTotal > $totalFunding + 0.009) {
            throw new InvalidArgumentException('Allocated amount exceeds available payment and credit.');
        }

        foreach ($allocations as $allocation) {
            $bill = StudentBill::query()
                ->where('id', $allocation['student_bill_id'])
                ->where('student_id', $studentId)
                ->first();

            if (! $bill) {
                throw new InvalidArgumentException('One or more selected bills are invalid for this student.');
            }
        }

        return $allocations;
    }

    private function buildAutoAllocations(int $studentId, float $paymentAmount): Collection
    {
        $remaining = $paymentAmount;
        $allocations = collect();

        $bills = StudentBill::query()
            ->with('billingItem')
            ->where('student_id', $studentId)
            ->where('balance', '>', 0)
            ->get()
            ->sortByDesc(fn (StudentBill $bill) => $bill->billingItem?->is_compulsory ? 1 : 0)
            ->values();

        foreach ($bills as $bill) {
            if ($remaining <= 0) {
                break;
            }

            $amount = min((float) $bill->balance, $remaining);
            $allocations->push([
                'student_bill_id' => $bill->id,
                'amount' => $amount,
            ]);
            $remaining -= $amount;
        }

        return $allocations;
    }

    private function outstandingBillsForStudent(Student $student)
    {
        return StudentBill::query()
            ->with(['billingItem', 'setup.academicTerm', 'setup.academicYear'])
            ->where('student_id', $student->id)
            ->where('balance', '>', 0)
            ->get()
            ->sortByDesc(fn (StudentBill $bill) => $bill->billingItem?->is_compulsory ? 1 : 0)
            ->values()
            ->map(fn (StudentBill $bill) => [
                'id' => $bill->id,
                'item_name' => $bill->billingItem?->name,
                'is_compulsory' => (bool) $bill->billingItem?->is_compulsory,
                'term_name' => $bill->setup?->academicTerm?->name,
                'year_name' => $bill->setup?->academicYear?->name,
                'amount_due' => (float) $bill->amount_due,
                'amount_paid' => (float) $bill->amount_paid,
                'balance' => (float) $bill->balance,
            ]);
    }

    private function generateReceiptNumber(): string
    {
        $year = now()->format('Y');
        $prefix = 'RCP-' . $year . '-';
        $last = BillPayment::query()
            ->where('receipt_no', 'like', $prefix . '%')
            ->orderByDesc('id')
            ->value('receipt_no');

        $sequence = 1;

        if ($last && preg_match('/-(\d+)$/', $last, $matches)) {
            $sequence = ((int) $matches[1]) + 1;
        }

        return $prefix . str_pad((string) $sequence, 5, '0', STR_PAD_LEFT);
    }

    private function generatePaystackReference(): string
    {
        $year = now()->format('Y');
        $prefix = 'BILL-' . $year . '-';
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

    private function successResponse(Request $request, BillPayment $payment, string $message)
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
            ]);
        }

        return redirect()->route('bill-payment-receipt', $payment->id)
            ->with('message_success', $message);
    }

    private function errorResponse(Request $request, string $message, int $status = 422)
    {
        if ($request->expectsJson()) {
            return response()->json(['message' => $message], $status);
        }

        return back()->with('message_error', $message);
    }
}
