<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SchoolClass extends Model
{
    protected $table = 'school_classes';

    protected $fillable = [
        'name',
        'class_category_id',
        'status',
        'class_teacher_id',
        'created_by',
        'updated_by',
    ];

    public function category()
    {
        return $this->belongsTo(ClassCategory::class, 'class_category_id');
    }

    public function students()
    {
        return $this->hasMany(Student::class, 'school_class_id');
    }

    public function classTeacher()
    {
        return $this->belongsTo(Staff::class, 'class_teacher_id');
    }

    public function courseTeachingAssignments()
    {
        return $this->hasMany(CourseTeachingAssignment::class, 'school_class_id');
    }

    public function courseRegistrations()
    {
        return $this->hasMany(CourseRegistration::class, 'school_class_id');
    }
}
