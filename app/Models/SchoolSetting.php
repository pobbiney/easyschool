<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSchool;
use App\Support\MediaUrl;
use App\Support\TenantContext;
use Illuminate\Database\Eloquent\Model;

class SchoolSetting extends Model
{
    use BelongsToSchool;

    protected $fillable = [
        'school_id',
        'name',
        'motto',
        'address',
        'phone',
        'email',
        'website',
        'logo_path',
        'default_academic_year_id',
        'default_academic_term_id',
        'updated_by',
    ];

    public static function current()
    {
        $schoolId = TenantContext::schoolId();

        if ($schoolId) {
            return static::query()->firstOrCreate(
                ['school_id' => $schoolId],
                ['name' => 'My School']
            );
        }

        return static::query()->withoutGlobalScopes()->first() ?? static::query()->make(['name' => 'EasySchool']);
    }

    public static function dummyLogoPath(): string
    {
        return 'assets/images/logo-icon.png';
    }

    public function logoUrl(): string
    {
        $path = $this->uploadedLogoPath();

        if ($path === null) {
            return asset(static::dummyLogoPath());
        }

        if (preg_match('#^https?://#i', $path) || str_starts_with($path, '//')) {
            return $path;
        }

        return asset($path);
    }

    public function logoFilePath(): string
    {
        $path = $this->uploadedLogoPath();

        if ($path === null || preg_match('#^https?://#i', $path) || str_starts_with($path, '//')) {
            return str_replace('\\', '/', public_path(static::dummyLogoPath()));
        }

        return str_replace('\\', '/', public_path($path));
    }

    public function uploadedLogoPath(): ?string
    {
        if (! filled($this->logo_path)) {
            return null;
        }

        $path = ltrim(str_replace('\\', '/', trim((string) $this->logo_path)), '/');

        if (preg_match('#^https?://#i', $path) || str_starts_with($path, '//')) {
            return MediaUrl::resolve($path);
        }

        return is_file(public_path($path)) ? $path : null;
    }

    public function defaultAcademicYear()
    {
        return $this->belongsTo(AcademicYear::class, 'default_academic_year_id');
    }

    public function defaultAcademicTerm()
    {
        return $this->belongsTo(AcademicTerm::class, 'default_academic_term_id');
    }

    public function defaultAcademicYearId(): ?int
    {
        $year = $this->defaultAcademicYear;

        if ($year && $year->status === 'Active') {
            return (int) $year->id;
        }

        return null;
    }

    public function defaultAcademicTermId(): ?int
    {
        $term = $this->defaultAcademicTerm;

        if ($term && $term->status === 'Active') {
            return (int) $term->id;
        }

        return null;
    }
}
