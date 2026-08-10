<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CourseTeachingAssignment extends Model
{
    protected $fillable = [
        'staff_id',
        'course_id',
        'school_class_id',
        'created_by',
        'updated_by',
    ];

    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id');
    }

    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class, 'school_class_id');
    }

    public function teacher()
    {
        return $this->belongsTo(Staff::class, 'staff_id');
    }
}
