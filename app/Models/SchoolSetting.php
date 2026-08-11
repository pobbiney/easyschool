<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SchoolSetting extends Model
{
    protected $fillable = [
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
        return static::firstOrCreate([]);
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
