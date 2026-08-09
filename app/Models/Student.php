<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    protected $fillable = [
        'student_id',
        'academic_year',
        'class_name',
        'section',
        'roll_number',
        'firstname',
        'othername',
        'surname',
        'category',
        'gender',
        'dob',
        'phone',
        'email',
        'picture',
        'father_name',
        'father_phone',
        'father_occupation',
        'father_photo',
        'mother_name',
        'mother_phone',
        'mother_occupation',
        'mother_photo',
        'guardian_type',
        'guardian_name',
        'guardian_email',
        'guardian_phone',
        'guardian_occupation',
        'guardian_address',
        'guardian_photo',
        'blood_group',
        'height',
        'weight',
        'has_nhis',
        'nhis_number',
        'nhis_card_name',
        'current_address',
        'previous_school_name',
        'notes',
        'house_id',
        'dormitory_id',
        'bed_id',
        'status',
        'created_by',
        'updated_by',
    ];

    public function docs()
    {
        return $this->hasMany(StudentDoc::class, 'student_id');
    }

    public function house()
    {
        return $this->belongsTo(House::class);
    }

    public function dormitory()
    {
        return $this->belongsTo(Dormitory::class);
    }

    public function bed()
    {
        return $this->belongsTo(DormitoryBed::class, 'bed_id');
    }

    public function getFullNameAttribute()
    {
        $name = trim($this->firstname . ' ' . $this->othername . ' ' . $this->surname);
        return preg_replace('/\s+/', ' ', $name);
    }
}
