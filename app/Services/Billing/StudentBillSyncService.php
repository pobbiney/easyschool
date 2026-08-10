<?php

namespace App\Services\Billing;

use App\Models\CategoryBillSetup;
use App\Models\Student;
use App\Models\StudentBill;
use Illuminate\Support\Collection;

class StudentBillSyncService
{
    public function syncForSetup(CategoryBillSetup $setup): array
    {
        $setup->load('items');

        $students = $this->matchingStudents($setup);
        $created = 0;
        $updated = 0;

        foreach ($students as $student) {
            foreach ($setup->items as $item) {
                if ((float) $item->amount <= 0) {
                    continue;
                }

                $bill = StudentBill::firstOrNew([
                    'student_id' => $student->id,
                    'category_bill_setup_id' => $setup->id,
                    'billing_item_id' => $item->billing_item_id,
                ]);

                $wasExisting = $bill->exists;
                $bill->amount_due = $item->amount;
                $bill->amount_paid = $bill->amount_paid ?? 0;
                $bill->refreshTotals();
                $bill->save();

                $wasExisting ? $updated++ : $created++;
            }
        }

        return [
            'students_matched' => $students->count(),
            'bills_created' => $created,
            'bills_updated' => $updated,
        ];
    }

    public function syncForStudent(Student $student): array
    {
        $student->load('schoolClass.category');

        if (! $student->school_class_id || ! $student->academic_year_id || ! $student->academic_term_id) {
            return ['students_matched' => 0, 'bills_created' => 0, 'bills_updated' => 0];
        }

        $categoryId = $student->schoolClass?->class_category_id;

        if (! $categoryId) {
            return ['students_matched' => 0, 'bills_created' => 0, 'bills_updated' => 0];
        }

        $setups = CategoryBillSetup::query()
            ->where('class_category_id', $categoryId)
            ->where('academic_year_id', $student->academic_year_id)
            ->where('academic_term_id', $student->academic_term_id)
            ->where('status', 'Active')
            ->with('items')
            ->get();

        $totals = ['students_matched' => 1, 'bills_created' => 0, 'bills_updated' => 0];

        foreach ($setups as $setup) {
            foreach ($setup->items as $item) {
                if ((float) $item->amount <= 0) {
                    continue;
                }

                $bill = StudentBill::firstOrNew([
                    'student_id' => $student->id,
                    'category_bill_setup_id' => $setup->id,
                    'billing_item_id' => $item->billing_item_id,
                ]);

                $wasExisting = $bill->exists;
                $bill->amount_due = $item->amount;
                $bill->amount_paid = $bill->amount_paid ?? 0;
                $bill->refreshTotals();
                $bill->save();

                $wasExisting ? $totals['bills_updated']++ : $totals['bills_created']++;
            }
        }

        return $totals;
    }

    private function matchingStudents(CategoryBillSetup $setup): Collection
    {
        return Student::query()
            ->where('status', 'Active')
            ->where('academic_year_id', $setup->academic_year_id)
            ->where('academic_term_id', $setup->academic_term_id)
            ->whereHas('schoolClass', function ($query) use ($setup) {
                $query->where('class_category_id', $setup->class_category_id);
            })
            ->get();
    }
}
