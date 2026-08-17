<?php

namespace App\Services\Sms;

use App\Models\SchoolClass;
use App\Models\Staff;
use App\Models\Student;
use App\Services\MNotifyService;
use App\Support\GhanaPhone;
use App\Support\TeacherCategory;
use RuntimeException;

class SchoolSmsService
{
    public function __construct(private MNotifyService $mnotify) {}

    /**
     * @return array{label: string, recipients: array<int, array{phone: string, name: string}>, skipped: int}
     */
    public function preview(string $audience, array $options = []): array
    {
        return $this->resolve($audience, $options);
    }

    /**
     * @return array{label: string, sent: int, skipped: int, recipient_count: int, response: array<string, mixed>|null}
     */
    public function send(string $audience, string $message, array $options = []): array
    {
        $resolved = $this->resolve($audience, $options);
        $phones = array_values(array_unique(array_column($resolved['recipients'], 'phone')));

        if ($phones === []) {
            throw new RuntimeException('No valid phone numbers were found for this audience.');
        }

        if (! $this->mnotify->isConfigured()) {
            throw new RuntimeException('SMS is not configured. Add an mNotify API key and sender ID in .env.');
        }

        $response = $this->mnotify->sendQuickSms($phones, $message);

        if ($response === null) {
            throw new RuntimeException('The SMS gateway could not send this message.');
        }

        return [
            'label' => $resolved['label'],
            'sent' => count($phones),
            'skipped' => $resolved['skipped'],
            'recipient_count' => count($phones),
            'response' => $response,
        ];
    }

    /**
     * @return array{label: string, recipients: array<int, array{phone: string, name: string}>, skipped: int}
     */
    private function resolve(string $audience, array $options = []): array
    {
        return match ($audience) {
            'teachers' => $this->fromStaff($this->teachers(), 'Teachers'),
            'staff' => $this->fromStaff(
                Staff::where('status', 'Active')->orderBy('surname')->get(),
                'All staff'
            ),
            'class' => $this->fromClass((int) ($options['school_class_id'] ?? 0)),
            'school' => $this->fromStudents(
                Student::where('status', 'Active')->orderBy('surname')->get(),
                'Entire school (parents / guardians)'
            ),
            'individual' => $this->fromIndividual(
                (string) ($options['target_type'] ?? ''),
                (int) ($options['target_id'] ?? 0)
            ),
            default => throw new RuntimeException('Choose who should receive this message.'),
        };
    }

    private function teachers()
    {
        return Staff::query()
            ->where('status', 'Active')
            ->where(function ($query) {
                $query->whereHas('user', function ($user) {
                    $user->where('user_cat', TeacherCategory::id());
                })->orWhereHas('hrPosition', function ($position) {
                    $position->where('name', 'Teacher');
                })->orWhere('position', 'Teacher');
            })
            ->orderBy('surname')
            ->get();
    }

    private function fromStaff($staffMembers, string $label): array
    {
        $recipients = [];
        $skipped = 0;

        foreach ($staffMembers as $staff) {
            $phone = GhanaPhone::normalize($staff->mobile);
            if (! $phone) {
                $skipped++;
                continue;
            }
            $recipients[$phone] = [
                'phone' => $phone,
                'name' => $staff->full_name,
            ];
        }

        return [
            'label' => $label,
            'recipients' => array_values($recipients),
            'skipped' => $skipped,
        ];
    }

    private function fromStudents($students, string $label): array
    {
        $recipients = [];
        $skipped = 0;

        foreach ($students as $student) {
            $phone = $this->studentPhone($student);
            if (! $phone) {
                $skipped++;
                continue;
            }
            $recipients[$phone] = [
                'phone' => $phone,
                'name' => 'Guardian of '.$student->full_name,
            ];
        }

        return [
            'label' => $label,
            'recipients' => array_values($recipients),
            'skipped' => $skipped,
        ];
    }

    private function fromClass(int $classId): array
    {
        $class = SchoolClass::find($classId);
        if (! $class) {
            throw new RuntimeException('Choose a class.');
        }

        $students = Student::where('status', 'Active')
            ->where('school_class_id', $class->id)
            ->orderBy('surname')
            ->get();

        return $this->fromStudents($students, $class->name.' (parents / guardians)');
    }

    private function fromIndividual(string $type, int $id): array
    {
        if ($type === 'staff') {
            $staff = Staff::find($id);
            if (! $staff) {
                throw new RuntimeException('Choose a staff member.');
            }

            return $this->fromStaff(collect([$staff]), $staff->full_name);
        }

        if ($type === 'student') {
            $student = Student::find($id);
            if (! $student) {
                throw new RuntimeException('Choose a student.');
            }

            return $this->fromStudents(collect([$student]), 'Guardian of '.$student->full_name);
        }

        throw new RuntimeException('Choose a staff member or student.');
    }

    private function studentPhone(Student $student): ?string
    {
        $phones = [];

        if ($student->guardian_phone) {
            $phones[] = $student->guardian_phone;
        }

        $guardianType = strtolower(trim((string) $student->guardian_type));
        if (str_contains($guardianType, 'father') && $student->father_phone) {
            array_unshift($phones, $student->father_phone);
        } elseif (str_contains($guardianType, 'mother') && $student->mother_phone) {
            array_unshift($phones, $student->mother_phone);
        }

        foreach ([$student->father_phone, $student->mother_phone, $student->phone] as $phone) {
            if ($phone) {
                $phones[] = $phone;
            }
        }

        foreach ($phones as $phone) {
            $normalized = GhanaPhone::normalize($phone);
            if ($normalized) {
                return $normalized;
            }
        }

        return null;
    }
}
