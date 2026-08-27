<?php

namespace App\Services\Tenant;

use App\Models\School;
use App\Models\SchoolActivityLog;
use Illuminate\Support\Facades\Auth;

class SchoolActivityLogger
{
    public function log(
        string $action,
        ?string $description = null,
        array $payload = [],
        ?int $schoolId = null,
        ?string $schoolCode = null,
        ?string $actorType = null,
        ?int $actorId = null,
    ): SchoolActivityLog {
        $schoolId ??= \App\Support\TenantContext::schoolId();
        $schoolCode ??= \App\Support\TenantContext::schoolCode();

        if ($actorType === null && $actorId === null) {
            if (Auth::guard('super_admin')->check()) {
                $actorType = 'super_admin';
                $actorId = Auth::guard('super_admin')->id();
            } elseif (Auth::guard('web')->check()) {
                $actorType = 'user';
                $actorId = Auth::guard('web')->id();
            } elseif (Auth::guard('parent')->check()) {
                $actorType = 'parent';
                $actorId = Auth::guard('parent')->id();
            }
        }

        return SchoolActivityLog::query()->create([
            'school_id' => $schoolId,
            'school_code' => $schoolCode,
            'actor_type' => $actorType,
            'actor_id' => $actorId,
            'action' => $action,
            'description' => $description,
            'payload' => $payload ?: null,
            'ip_address' => request()->ip(),
        ]);
    }
}
