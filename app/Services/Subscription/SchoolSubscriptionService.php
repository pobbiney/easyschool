<?php

namespace App\Services\Subscription;

use App\Models\AcademicTermCalendar;
use App\Models\School;
use App\Models\SchoolSetting;
use App\Models\SchoolSubscriptionPayment;
use App\Models\Subscription;
use App\Services\MNotifyService;
use App\Support\GhanaPhone;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use RuntimeException;

class SchoolSubscriptionService
{
    public const SUSPENSION_REASON_SUBSCRIPTION = 'subscription';

    public const SUSPENSION_REASON_ADMIN = 'admin';

    public const GRACE_DAYS = 10;

    public const NOTICE_DAYS = 7;

    public function __construct(private MNotifyService $mnotify) {}

    public function plan(): ?Subscription
    {
        return Subscription::query()->orderBy('id')->first();
    }

    public function currentCalendar(School $school): ?AcademicTermCalendar
    {
        $settings = $school->settings()->withoutGlobalScopes()->first();

        $schoolIds = array_values(array_unique(array_filter([
            $school->id,
            $settings?->school_id,
        ])));

        if ($schoolIds === []) {
            return null;
        }

        return AcademicTermCalendar::query()
            ->withoutGlobalScopes()
            ->whereIn('school_id', $schoolIds)
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->first();
    }

    public function currentPackage(School $school): ?SchoolSubscriptionPayment
    {
        $activated = SchoolSubscriptionPayment::query()
            ->where('school_id', $school->id)
            ->where('status', SchoolSubscriptionPayment::STATUS_ACTIVATED)
            ->orderByDesc('id')
            ->first();

        if ($activated) {
            return $activated;
        }

        return SchoolSubscriptionPayment::query()
            ->where('school_id', $school->id)
            ->orderByDesc('id')
            ->first();
    }

    public function accessEndsOn(CarbonInterface $vacationDate): CarbonInterface
    {
        return $vacationDate->copy()->startOfDay()->addDays(self::GRACE_DAYS);
    }

    public function isVacationDue(School $school): bool
    {
        $vacation = $this->currentCalendar($school)?->vacation_date;

        if (! $vacation) {
            return false;
        }

        return now()->startOfDay()->gt($this->accessEndsOn($vacation));
    }

    public function isSubscriptionExpired(School $school): bool
    {
        if (! $this->isVacationDue($school)) {
            return false;
        }

        $calendar = $this->currentCalendar($school);
        $vacation = $calendar?->vacation_date;

        if (! $vacation) {
            return false;
        }

        return ! $this->hasActivationCoveringVacation($school, $vacation, $calendar);
    }

    public function hasActivationCoveringVacation(
        School $school,
        CarbonInterface $vacationDate,
        ?AcademicTermCalendar $calendar = null,
    ): bool {
        $package = $this->currentPackage($school);

        if (! $package?->isActivated() || ! $package->activated_at) {
            return false;
        }

        if ($package->activated_at->lt($vacationDate->copy()->startOfDay())) {
            return false;
        }

        $calendar ??= $this->currentCalendar($school);

        if ($calendar?->updated_at && $package->activated_at->lt($calendar->updated_at)) {
            return false;
        }

        return true;
    }

    /**
     * @return array{phase: string, vacation_date: \Carbon\CarbonInterface, access_ends_on: \Carbon\CarbonInterface, days_left: int}|null
     */
    public function subscriptionNotice(School $school): ?array
    {
        if ($school->isSuspendedByAdmin() || $school->isPending() || $school->status === School::STATUS_REJECTED) {
            return null;
        }

        $calendar = $this->currentCalendar($school);
        $vacation = $calendar?->vacation_date;

        if (! $vacation) {
            return null;
        }

        $vacationDay = $vacation->copy()->startOfDay();
        $accessEnds = $this->accessEndsOn($vacation);
        $today = now()->startOfDay();

        if ($today->gte($vacationDay)) {
            return [
                'phase' => 'ended',
                'vacation_date' => $vacationDay,
                'access_ends_on' => $accessEnds,
                'days_left' => max(0, (int) $today->diffInDays($accessEnds, false)),
            ];
        }

        $warnFrom = $vacationDay->copy()->subDays(self::NOTICE_DAYS);

        if ($today->gte($warnFrom)) {
            return [
                'phase' => 'upcoming',
                'vacation_date' => $vacationDay,
                'access_ends_on' => $accessEnds,
                'days_left' => (int) $today->diffInDays($vacationDay),
            ];
        }

        return null;
    }

    public function suspendIfSubscriptionExpired(School $school): bool
    {
        if ($school->isSuspendedByAdmin() || ! $this->isSubscriptionExpired($school)) {
            return false;
        }

        if ($school->isSuspendedForSubscription()) {
            return true;
        }

        $school->update([
            'status' => School::STATUS_SUSPENDED,
            'suspension_reason' => self::SUSPENSION_REASON_SUBSCRIPTION,
        ]);

        return true;
    }

    public function updateTermDates(
        School $school,
        int $yearId,
        int $termId,
        string $openingDate,
        string $vacationDate,
    ): AcademicTermCalendar {
        $calendar = AcademicTermCalendar::query()
            ->withoutGlobalScopes()
            ->updateOrCreate(
                [
                    'school_id' => $school->id,
                    'academic_year_id' => $yearId,
                    'academic_term_id' => $termId,
                ],
                [
                    'opening_date' => $openingDate,
                    'vacation_date' => $vacationDate,
                ]
            );

        SchoolSetting::query()
            ->withoutGlobalScopes()
            ->where('school_id', $school->id)
            ->update([
                'default_academic_year_id' => $yearId,
                'default_academic_term_id' => $termId,
            ]);

        $school->unsetRelation('settings');
        $this->syncAccessFromVacation($school->fresh());

        return $calendar->fresh();
    }

    public function syncAccessFromVacation(School $school): void
    {
        if ($school->isPending() || $school->status === School::STATUS_REJECTED || $school->isSuspendedByAdmin()) {
            return;
        }

        if ($this->isSubscriptionExpired($school)) {
            $this->suspendIfSubscriptionExpired($school);

            return;
        }

        if ($school->isSuspendedForSubscription()) {
            $school->update([
                'status' => School::STATUS_APPROVED,
                'suspension_reason' => null,
            ]);
        }
    }

    public function schoolCanPay(School $school): bool
    {
        if ($school->isPending() || $school->status === School::STATUS_REJECTED) {
            return false;
        }

        if ($school->isSuspendedByAdmin()) {
            return false;
        }

        return $school->isApproved() || $school->isSuspendedForSubscription();
    }

    public function expireDueSchools(): int
    {
        $count = 0;

        School::query()
            ->where('status', School::STATUS_APPROVED)
            ->with('settings')
            ->orderBy('id')
            ->each(function (School $school) use (&$count) {
                if (! $this->suspendIfSubscriptionExpired($school)) {
                    return;
                }

                $count++;
            });

        return $count;
    }

    public function nextReference(): string
    {
        $year = now()->format('Y');
        $prefix = 'SUB-'.$year.'-';

        $last = SchoolSubscriptionPayment::query()
            ->where('paystack_reference', 'like', $prefix.'%')
            ->orderByDesc('id')
            ->value('paystack_reference');

        $sequence = 1;

        if (is_string($last) && preg_match('/^SUB-\d{4}-(\d+)$/', $last, $matches)) {
            $sequence = ((int) $matches[1]) + 1;
        }

        do {
            $reference = $prefix.str_pad((string) $sequence, 5, '0', STR_PAD_LEFT);
            $sequence++;
        } while (SchoolSubscriptionPayment::query()->where('paystack_reference', $reference)->exists());

        return $reference;
    }

    public function markPaid(SchoolSubscriptionPayment $payment, array $paystackData): SchoolSubscriptionPayment
    {
        if ($payment->isActivated() || $payment->isPaid()) {
            return $payment;
        }

        $payment->update([
            'status' => SchoolSubscriptionPayment::STATUS_PAID,
            'paid_at' => now(),
            'paystack_transaction_id' => isset($paystackData['id']) ? (string) $paystackData['id'] : $payment->paystack_transaction_id,
            'paystack_channel' => $paystackData['channel'] ?? $payment->paystack_channel,
            'gateway_response' => $paystackData,
        ]);

        $this->notifyPayer($payment->fresh(['school']));

        return $payment->fresh();
    }

    public function notifyPayer(SchoolSubscriptionPayment $payment): bool
    {
        if ($payment->sms_sent_at) {
            return true;
        }

        $phone = GhanaPhone::normalize($payment->payer_phone);

        if (! $phone || ! $this->mnotify->isConfigured()) {
            return false;
        }

        $schoolName = trim((string) ($payment->school?->name ?: 'your school'));
        if (mb_strlen($schoolName) > 40) {
            $schoolName = mb_substr($schoolName, 0, 37).'...';
        }

        $message = "{$schoolName}: Payment received. Activation reference: {$payment->paystack_reference}. Enter it to activate the school account.";

        try {
            $sent = $this->mnotify->sendQuickSms([$phone], $message) !== null;
        } catch (\Throwable $e) {
            report($e);

            return false;
        }

        if ($sent) {
            $payment->update(['sms_sent_at' => now()]);
        }

        return $sent;
    }

    public function activateByReference(string $reference): School
    {
        $reference = strtoupper(trim($reference));

        $payment = SchoolSubscriptionPayment::query()
            ->where('paystack_reference', $reference)
            ->first();

        if (! $payment) {
            throw new InvalidArgumentException('That reference number was not found.');
        }

        if ($payment->isActivated()) {
            throw new InvalidArgumentException('This reference has already been used to activate a school.');
        }

        if (! $payment->isPaid()) {
            throw new InvalidArgumentException('This payment has not been confirmed yet. Complete Paystack checkout first.');
        }

        return DB::transaction(function () use ($payment) {
            $school = School::query()->lockForUpdate()->findOrFail($payment->school_id);

            if ($school->isSuspendedByAdmin()) {
                throw new RuntimeException('This school was suspended by the platform administrator and cannot be activated here.');
            }

            $school->update([
                'status' => School::STATUS_APPROVED,
                'suspension_reason' => null,
            ]);

            $payment->update([
                'status' => SchoolSubscriptionPayment::STATUS_ACTIVATED,
                'activated_at' => now(),
            ]);

            return $school->fresh();
        });
    }
}
