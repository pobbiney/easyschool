<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AcademicAssessment extends Model
{
    public const TYPES = ['homework', 'class_test', 'exam', 'class_assignment'];

    public const STATUSES = ['draft', 'published'];

    protected $fillable = [
        'type',
        'title',
        'description',
        'due_date',
        'assessment_date',
        'school_class_id',
        'course_id',
        'academic_year_id',
        'academic_term_id',
        'staff_id',
        'max_score',
        'status',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'due_date' => 'date',
        'assessment_date' => 'date',
        'max_score' => 'decimal:2',
    ];

    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class, 'school_class_id');
    }

    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id');
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class, 'academic_year_id');
    }

    public function academicTerm()
    {
        return $this->belongsTo(AcademicTerm::class, 'academic_term_id');
    }

    public function teacher()
    {
        return $this->belongsTo(Staff::class, 'staff_id');
    }

    public function scores()
    {
        return $this->hasMany(AssessmentScore::class, 'academic_assessment_id');
    }

    public function typeLabel(): string
    {
        return match ($this->type) {
            'homework' => 'Homework',
            'class_test' => 'Class Test',
            'exam' => 'Exam',
            'class_assignment' => 'Class Assignment',
            default => ucfirst(str_replace('_', ' ', $this->type)),
        };
    }
}
