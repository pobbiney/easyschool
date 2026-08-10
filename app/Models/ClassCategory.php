<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClassCategory extends Model
{
    protected $fillable = [
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
}
