<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserExtraLink extends Model
{
    protected $table = 'user_extra_links';
    public $incrementing = false;
    public $timestamps = false;

    protected $casts = [
        'user_id' => 'int',
        'link_id' => 'int',
    ];

    protected $fillable = [
        'user_id',
        'link_id',
    ];
}
