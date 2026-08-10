<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Staff extends Model
{
   public function country()
    {
        return $this->belongsTo(Country::class, 'nationality');
    }

    public function user()
    {
        return $this->hasOne(User::class, 'staff_id', 'id');
    }
}
