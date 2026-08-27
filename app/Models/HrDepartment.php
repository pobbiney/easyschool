<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;

class HrDepartment extends Model
{
    use BelongsToSchool;

    protected $fillable = [
        'school_id','name', 'code', 'status', 'created_by', 'updated_by'];

    public function positions()
    {
        return $this->hasMany(HrPosition::class, 'department_id');
    }

    public function staff()
    {
        return $this->hasMany(Staff::class, 'department_id');
    }
}
