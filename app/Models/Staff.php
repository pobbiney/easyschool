<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Staff extends Model
{
    public function getFullNameAttribute(): string
    {
        return trim(collect([$this->title, $this->firstname, $this->othername, $this->surname])->filter()->implode(' '));
    }

   public function country()
    {
        return $this->belongsTo(Country::class, 'nationality');
    }

    public function user()
    {
        return $this->hasOne(User::class, 'staff_id', 'id');
    }

    public function assignedClass()
    {
        return $this->hasOne(SchoolClass::class, 'class_teacher_id');
    }

    public function courseTeachingAssignments()
    {
        return $this->hasMany(CourseTeachingAssignment::class, 'staff_id');
    }
}
