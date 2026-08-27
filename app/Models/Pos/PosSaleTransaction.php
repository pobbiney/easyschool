<?php

namespace App\Models\Pos;

use App\Models\Student;
use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;

class PosSaleTransaction extends Model
{
    use BelongsToSchool;

    public const STATUS_PENDING = 'pending';

    public const STATUS_SUCCESS = 'success';

    public const STATUS_FAILED = 'failed';

    protected $fillable = [
        'school_id',
        'reference',
        'subtotal',
        'discount',
        'amount',
        'currency',
        'status',
        'student_id',
        'customer_name',
        'customer_phone',
        'notes',
        'cart_items',
        'paystack_transaction_id',
        'paystack_channel',
        'gateway_response',
        'pos_sale_id',
        'created_by',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'discount' => 'decimal:2',
        'amount' => 'decimal:2',
        'cart_items' => 'array',
        'gateway_response' => 'array',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function sale()
    {
        return $this->belongsTo(PosSale::class, 'pos_sale_id');
    }
}
