<?php

namespace App\Support;

class TenantCodePrefix
{
    public static function segment(): string
    {
        $code = TenantContext::schoolCode();

        return $code ? strtoupper($code).'-': '';
    }

    public static function with(string $suffix): string
    {
        return static::segment().$suffix;
    }
}
