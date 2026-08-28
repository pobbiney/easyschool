<?php

namespace App\Models\Pos;

use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;

class PosCategory extends Model
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

    public function products()
    {
        return $this->hasMany(PosProduct::class, 'pos_category_id');
    }

    public function isInUse(): bool
    {
        if (isset($this->products_count)) {
            return (int) $this->products_count > 0;
        }

        return $this->products()->exists();
    }
}
