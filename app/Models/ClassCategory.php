<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;

class ClassCategory extends Model
{
    use BelongsToSchool;

    protected $fillable = [
        'school_id',
        'name',
        'description',
        'status',
        'created_by',
        'updated_by',
    ];

    public function schoolClasses()
    {
        return $this->hasMany(SchoolClass::class, 'class_category_id');
    }

    public function courses()
    {
        return $this->belongsToMany(Course::class, 'class_category_course', 'class_category_id', 'course_id');
    }
}
