<?php

namespace App\Models\Expense;

use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;

class ExpenseCategory extends Model
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

    public function expenses()
    {
        return $this->hasMany(Expense::class, 'expense_category_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'Active');
    }
}
