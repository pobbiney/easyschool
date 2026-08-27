<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;

class House extends Model
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

    public function dormitories()
    {
        return $this->hasMany(Dormitory::class);
    }

    public function students()
    {
        return $this->hasMany(Student::class);
    }
}
