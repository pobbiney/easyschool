<?php

namespace App\Console\Commands;

use App\Services\Subscription\SchoolSubscriptionService;
use Illuminate\Console\Command;

class ExpireSchoolSubscriptions extends Command
{
    protected $signature = 'subscriptions:expire';

    protected $description = 'Suspend schools whose vacation date plus the 10-day grace period has ended. Run daily via `php artisan schedule:run` (Windows Task Scheduler every minute).';

    public function handle(SchoolSubscriptionService $subscriptions): int
    {
        $count = $subscriptions->expireDueSchools();

        $this->info("Suspended {$count} school(s) whose vacation grace period has ended.");

        return self::SUCCESS;
    }
}
