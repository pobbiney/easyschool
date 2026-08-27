<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;

class Dormitory extends Model
{
    use BelongsToSchool;

    protected $fillable = [
        'school_id',
        'house_id',
        'name',
        'bed_count',
        'status',
        'created_by',
        'updated_by',
    ];

    public function house()
    {
        return $this->belongsTo(House::class);
    }

    public function beds()
    {
        return $this->hasMany(DormitoryBed::class);
    }

    public function students()
    {
        return $this->hasMany(Student::class);
    }
}
