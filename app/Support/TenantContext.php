<?php

namespace App\Support;

use App\Models\School;

class TenantContext
{
    public const SESSION_SCHOOL_ID = 'tenant.school_id';

    public const SESSION_SCHOOL_CODE = 'tenant.school_code';

    public const SESSION_SUPER_ADMIN_VIEWING = 'tenant.super_admin_viewing';

    protected static bool $scopeEnabled = true;

    protected static ?int $forcedSchoolId = null;

    protected static ?string $forcedSchoolCode = null;

    public static function disableScope(): void
    {
        static::$scopeEnabled = false;
    }

    public static function enableScope(): void
    {
        static::$scopeEnabled = true;
    }

    public static function shouldApplyScope(): bool
    {
        if (! static::$scopeEnabled) {
            return false;
        }

        return static::schoolId() !== null;
    }

    public static function schoolId(): ?int
    {
        if (static::$forcedSchoolId !== null) {
            return static::$forcedSchoolId;
        }

        $id = session(static::SESSION_SCHOOL_ID);

        return $id ? (int) $id : null;
    }

    public static function schoolCode(): ?string
    {
        if (static::$forcedSchoolCode !== null) {
            return static::$forcedSchoolCode;
        }

        $code = session(static::SESSION_SCHOOL_CODE);

        return $code ? (string) $code : null;
    }

    public static function setSchool(School $school, bool $superAdminViewing = false): void
    {
        session([
            static::SESSION_SCHOOL_ID => $school->id,
            static::SESSION_SCHOOL_CODE => $school->code,
            static::SESSION_SUPER_ADMIN_VIEWING => $superAdminViewing,
        ]);

        static::$forcedSchoolId = null;
        static::$forcedSchoolCode = null;
    }

    public static function clear(): void
    {
        session()->forget([
            static::SESSION_SCHOOL_ID,
            static::SESSION_SCHOOL_CODE,
            static::SESSION_SUPER_ADMIN_VIEWING,
        ]);

        static::$forcedSchoolId = null;
        static::$forcedSchoolCode = null;
    }

    public static function isSuperAdminViewing(): bool
    {
        return (bool) session(static::SESSION_SUPER_ADMIN_VIEWING, false);
    }

    public static function forceSchool(?int $schoolId, ?string $schoolCode = null): void
    {
        static::$forcedSchoolId = $schoolId;
        static::$forcedSchoolCode = $schoolCode;
    }

    public static function runWithoutScope(callable $callback): mixed
    {
        $previous = static::$scopeEnabled;
        static::$scopeEnabled = false;

        try {
            return $callback();
        } finally {
            static::$scopeEnabled = $previous;
        }
    }

    public static function resolveSchoolByCode(string $code): ?School
    {
        return School::query()
            ->where('code', strtoupper(trim($code)))
            ->first();
    }
}
