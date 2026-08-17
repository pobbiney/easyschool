<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HrLeaveType extends Model
{
    protected $fillable = ['name', 'days_per_year', 'is_paid', 'gender_limit', 'status', 'created_by'];

    protected $casts = [
        'is_paid' => 'boolean',
        'days_per_year' => 'integer',
    ];

    public function requests()
    {
        return $this->hasMany(HrLeaveRequest::class, 'leave_type_id');
    }
}
