<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    protected $fillable = [
        'name',
        'amount',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];
}
