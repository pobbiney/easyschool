<?php

namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use App\Models\BillPayment;
use App\Models\BillPaymentAllocation;
use App\Models\SchoolSetting;
use App\Models\Student;
use App\Models\StudentBill;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BillPaymentController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|in:Cash,Mobile Money,Bank,Cheque',
            'reference' => 'nullable|string|max:100',
            'paid_at' => 'required|date',
            'notes' => 'nullable|string|max:500',
            'allocations' => 'nullable|array',
            'allocations.*.student_bill_id' => 'required|exists:student_bills,id',
            'allocations.*.amount' => 'required|numeric|min:0.01',
        ]);

        $student = Student::findOrFail($validated['student_id']);
        $paymentAmount = (float) $validated['amount'];

        try {
            $payment = DB::transaction(function () use ($validated, $student, $paymentAmount) {
            $allocations = collect($validated['allocations'] ?? []);

            if ($allocations->isEmpty()) {
                $allocations = $this->buildAutoAllocations($student->id, $paymentAmount);
            }

            $allocatedTotal = round($allocations->sum('amount'), 2);

            if ($allocatedTotal <= 0) {
                throw new \InvalidArgumentException('No outstanding bills to allocate this payment to.');
            }

            if ($allocatedTotal > $paymentAmount + 0.009) {
                throw new \InvalidArgumentException('Allocated amount exceeds payment amount.');
            }

            $payment = BillPayment::create([
                'student_id' => $student->id,
                'receipt_no' => $this->generateReceiptNumber(),
                'amount' => $paymentAmount,
                'payment_method' => $validated['payment_method'],
                'reference' => $validated['reference'] ?? null,
                'paid_at' => $validated['paid_at'],
                'notes' => $validated['notes'] ?? null,
                'created_by' => Auth::id(),
            ]);

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

            return $payment->load(['student', 'allocations.studentBill.billingItem']);
        });
        } catch (\InvalidArgumentException $e) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $e->getMessage()], 422);
            }

            return back()->with('message_error', $e->getMessage());
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Payment recorded successfully.',
                'payment_id' => $payment->id,
                'receipt_no' => $payment->receipt_no,
                'receipt_url' => route('bill-payment-receipt', $payment->id),
            ]);
        }

        return redirect()->route('bill-payment-receipt', $payment->id)
            ->with('message_success', 'Payment recorded successfully.');
    }

    public function receipt($id)
    {
        $payment = BillPayment::with([
            'student.schoolClass.category',
            'allocations.studentBill.billingItem',
        ])->findOrFail($id);

        return view('billing.payment-receipt', [
            'payment' => $payment,
            'school' => SchoolSetting::current(),
        ]);
    }

    private function buildAutoAllocations(int $studentId, float $paymentAmount)
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
}
