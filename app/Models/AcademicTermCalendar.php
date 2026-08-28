<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;

class AcademicTermCalendar extends Model
{
    use BelongsToSchool;

    protected $fillable = [
        'school_id',
        'academic_year_id',
        'academic_term_id',
        'opening_date',
        'vacation_date',
        'updated_by',
    ];

    protected $casts = [
        'opening_date' => 'date',
        'vacation_date' => 'date',
    ];

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function academicTerm()
    {
        return $this->belongsTo(AcademicTerm::class);
    }
}
