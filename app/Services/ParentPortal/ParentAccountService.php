<?php

namespace App\Services\ParentPortal;

use App\Models\ParentPortal\ParentAccount;
use App\Models\Student;

class ParentAccountService
{
    public function __construct(private ParentStudentService $parentStudentService) {}

    public function syncFromStudent(Student $student): ?ParentAccount
    {
        if ($student->status !== 'Active') {
            return null;
        }

        $phone = $this->parentStudentService->normalizePhone($student->guardian_phone);

        if (! $phone) {
            return null;
        }

        $existing = ParentAccount::query()
            ->where('school_id', $student->school_id)
            ->where('phone', $phone)
            ->first();

        if ($existing) {
            $existing->update([
                'guardian_name' => $student->guardian_name ?: $existing->guardian_name,
            ]);

            return $existing;
        }

        return ParentAccount::create([
            'school_id' => $student->school_id,
            'phone' => $phone,
            'guardian_name' => $student->guardian_name,
            'password' => (string) config('parent.default_password', 'Parent123'),
            'status' => ParentAccount::STATUS_ACTIVE,
            'must_change_password' => false,
        ]);
    }

    public function syncAllActiveStudents(): int
    {
        $count = 0;

        Student::query()
            ->where('status', 'Active')
            ->orderBy('id')
            ->chunkById(100, function ($students) use (&$count) {
                foreach ($students as $student) {
                    if ($this->syncFromStudent($student)) {
                        $count++;
                    }
                }
            });

        return $count;
    }

    public function resetToDefault(string $phone): ?ParentAccount
    {
        $query = ParentAccount::query()
            ->where('phone', $phone)
            ->where('status', ParentAccount::STATUS_ACTIVE);

        if ($schoolId = \App\Support\TenantContext::schoolId()) {
            $query->where('school_id', $schoolId);
        }

        $account = $query->first();

        if (! $account) {
            return null;
        }

        $account->update([
            'password' => (string) config('parent.default_password', 'Parent123'),
            'must_change_password' => true,
        ]);

        return $account->fresh();
    }
}
