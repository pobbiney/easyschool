<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;

class AcademicYear extends Model
{
    use BelongsToSchool;

    protected $fillable = [
        'school_id',
        'name',
        'status',
        'created_by',
        'updated_by',
    ];

    public function courseRegistrations()
    {
        return $this->hasMany(CourseRegistration::class, 'academic_year_id');
    }
}
