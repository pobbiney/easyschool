<?php

namespace App\Services\Tenant;

use App\Models\School;
use App\Services\MNotifyService;
use App\Support\GhanaPhone;

class SchoolRegistrationSmsService
{
    public function __construct(private MNotifyService $mnotify) {}

    public function notifySubmitted(School $school): bool
    {
        $schoolName = $this->shortName($school);

        return $this->send(
            $school,
            "EasySchool: Thank you for registering {$schoolName}. You will be notified when your registration is approved."
        );
    }

    public function notifyApproved(School $school): bool
    {
        $schoolName = $this->shortName($school);
        $code = trim((string) $school->code);
        $email = trim((string) $school->admin_email);

        $message = "{$schoolName}: Your registration has been approved. School code: {$code}. Login with the email {$email} and the password you set during registration.";

        return $this->send($school, $message, registrantOnly: true);
    }

    private function send(School $school, string $message, bool $registrantOnly = false): bool
    {
        $phones = $this->recipientPhones($school, $registrantOnly);

        if ($phones === [] || ! $this->mnotify->isConfigured()) {
            return false;
        }

        try {
            return $this->mnotify->sendQuickSms($phones, $message) !== null;
        } catch (\Throwable $e) {
            report($e);

            return false;
        }
    }

    /**
     * @return list<string>
     */
    private function recipientPhones(School $school, bool $registrantOnly = false): array
    {
        $candidates = [$school->admin_phone, $school->phone];

        return collect($candidates)
            ->map(fn ($phone) => GhanaPhone::normalize($phone))
            ->filter()
            ->unique()
            ->when($registrantOnly, fn ($phones) => $phones->take(1))
            ->values()
            ->all();
    }

    private function shortName(School $school): string
    {
        $name = trim((string) $school->name);

        if (mb_strlen($name) > 40) {
            return mb_substr($name, 0, 37).'...';
        }

        return $name !== '' ? $name : 'your school';
    }
}
