<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StaffDoc extends Model
{
 protected $fillable = ['staff_id', 'level', 'year','qualification','document_path','created_by'];
}
