<?php

namespace App\Services\Tenant;

use App\Models\School;
use Illuminate\Support\Str;

class SchoolCodeGenerator
{
    public function generate(): string
    {
        $year = now()->format('Y');

        do {
            $suffix = strtoupper(Str::random(4));
            $code = "SCH-{$year}-{$suffix}";
        } while (School::query()->where('code', $code)->exists());

        return $code;
    }
}
