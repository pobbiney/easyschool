<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SchoolSetting extends Model
{
    protected $fillable = [
        'name',
        'motto',
        'address',
        'phone',
        'email',
        'website',
        'logo_path',
        'updated_by',
    ];

    public static function current()
    {
        return static::firstOrCreate([]);
    }
}
