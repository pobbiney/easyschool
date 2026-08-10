<?php

namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use App\Models\AcademicTerm;
use App\Models\AcademicYear;
use App\Models\BillPayment;
use App\Models\BillPaymentAllocation;
use App\Models\ClassCategory;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\StudentBill;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StudentBillController extends Controller
{
    public function index(Request $request)
    {
        $students = Student::query()
            ->with(['schoolClass.category', 'academicYear', 'academicTerm'])
            ->where('status', 'Active')
            ->when($request->academic_term_id, fn ($q, $v) => $q->where('academic_term_id', $v))
            ->when($request->academic_year_id, fn ($q, $v) => $q->where('academic_year_id', $v))
            ->when($request->class_category_id, function ($q, $v) {
                $q->whereHas('schoolClass', fn ($classQuery) => $classQuery->where('class_category_id', $v));
            })
            ->when($request->school_class_id, fn ($q, $v) => $q->where('school_class_id', $v))
            ->orderBy('surname')
            ->orderBy('firstname')
            ->get()
            ->map(function (Student $student) use ($request) {
                $billsQuery = StudentBill::query()->where('student_id', $student->id);

                if ($request->academic_term_id || $request->academic_year_id || $request->class_category_id) {
                    $billsQuery->whereHas('setup', function ($setupQuery) use ($request, $student) {
                        if ($request->academic_term_id) {
                            $setupQuery->where('academic_term_id', $request->academic_term_id);
                        }
                        if ($request->academic_year_id) {
                            $setupQuery->where('academic_year_id', $request->academic_year_id);
                        }
                        if ($request->class_category_id) {
                            $setupQuery->where('class_category_id', $request->class_category_id);
                        } elseif ($student->schoolClass?->class_category_id) {
                            $setupQuery->where('class_category_id', $student->schoolClass->class_category_id);
                        }
                    });
                }

                $bills = $billsQuery->get();
                $totalDue = $bills->sum('amount_due');
                $totalPaid = $bills->sum('amount_paid');
                $balance = $bills->sum('balance');

                $status = 'No Bills';
                if ($bills->isNotEmpty()) {
                    if ($balance <= 0 && $totalPaid > 0) {
                        $status = 'Paid';
                    } elseif ($totalPaid > 0) {
                        $status = 'Partial';
                    } else {
                        $status = 'Pending';
                    }
                }

                return (object) [
                    'student' => $student,
                    'total_due' => $totalDue,
                    'total_paid' => $totalPaid,
                    'balance' => $balance,
                    'status' => $status,
                ];
            })
            ->when($request->status, function ($collection) use ($request) {
                return $collection->filter(fn ($row) => $row->status === $request->status);
            })
            ->values();

        $allBills = StudentBill::query()->get();

        return view('billing.student-bills', [
            'rows' => $students,
            'classCategories' => ClassCategory::where('status', 'Active')->orderBy('name')->get(),
            'academicTerms' => AcademicTerm::where('status', 'Active')->orderBy('sort_order')->get(),
            'academicYears' => AcademicYear::where('status', 'Active')->orderBy('name', 'desc')->get(),
            'schoolClasses' => SchoolClass::where('status', 'Active')->orderBy('name')->get(),
            'filters' => $request->only(['academic_term_id', 'academic_year_id', 'class_category_id', 'school_class_id', 'status']),
            'stats' => [
                'students' => $students->count(),
                'total_due' => $allBills->sum('amount_due'),
                'total_paid' => $allBills->sum('amount_paid'),
                'outstanding' => $allBills->sum('balance'),
            ],
        ]);
    }

    public function show($id)
    {
        $student = Student::with(['schoolClass.category', 'academicYear', 'academicTerm'])->findOrFail($id);

        $bills = StudentBill::query()
            ->with(['billingItem', 'setup.academicTerm', 'setup.academicYear', 'setup.classCategory'])
            ->where('student_id', $student->id)
            ->orderBy('id')
            ->get()
            ->map(function (StudentBill $bill) {
                return [
                    'id' => $bill->id,
                    'item_name' => $bill->billingItem?->name,
                    'is_compulsory' => (bool) $bill->billingItem?->is_compulsory,
                    'category_name' => $bill->setup?->classCategory?->name,
                    'term_name' => $bill->setup?->academicTerm?->name,
                    'year_name' => $bill->setup?->academicYear?->name,
                    'amount_due' => $bill->amount_due,
                    'amount_paid' => $bill->amount_paid,
                    'balance' => $bill->balance,
                    'status' => $bill->status,
                ];
            });

        return response()->json([
            'student' => [
                'id' => $student->id,
                'student_id' => $student->student_id,
                'full_name' => $student->full_name,
                'class_name' => $student->class_name,
                'category_name' => $student->schoolClass?->category?->name,
            ],
            'bills' => $bills,
            'summary' => [
                'total_due' => $bills->sum('amount_due'),
                'total_paid' => $bills->sum('amount_paid'),
                'balance' => $bills->sum('balance'),
            ],
        ]);
    }

    public function outstanding($id)
    {
        $student = Student::findOrFail($id);

        $bills = StudentBill::query()
            ->with('billingItem')
            ->where('student_id', $student->id)
            ->where('balance', '>', 0)
            ->orderBy('id')
            ->get()
            ->map(fn (StudentBill $bill) => [
                'id' => $bill->id,
                'item_name' => $bill->billingItem?->name,
                'balance' => $bill->balance,
            ]);

        return response()->json([
            'student_id' => $student->id,
            'full_name' => $student->full_name,
            'bills' => $bills,
            'total_outstanding' => $bills->sum('balance'),
        ]);
    }
}
