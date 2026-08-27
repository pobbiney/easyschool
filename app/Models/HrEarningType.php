<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;

class HrEarningType extends Model
{
    use BelongsToSchool;

    protected $fillable = [
        'school_id','name', 'code', 'method', 'default_amount', 'is_taxable', 'status'];

    protected $casts = [
        'default_amount' => 'decimal:2',
        'is_taxable' => 'boolean',
    ];
}
