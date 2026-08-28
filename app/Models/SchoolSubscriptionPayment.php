<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SchoolSubscriptionPayment extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_PAID = 'paid';

    public const STATUS_ACTIVATED = 'activated';

    protected $fillable = [
        'school_id',
        'subscription_id',
        'amount',
        'payer_full_name',
        'payer_phone',
        'payer_email',
        'paystack_reference',
        'status',
        'paid_at',
        'activated_at',
        'sms_sent_at',
        'paystack_transaction_id',
        'paystack_channel',
        'gateway_response',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
        'activated_at' => 'datetime',
        'sms_sent_at' => 'datetime',
        'gateway_response' => 'array',
    ];

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isPaid(): bool
    {
        return $this->status === self::STATUS_PAID;
    }

    public function isActivated(): bool
    {
        return $this->status === self::STATUS_ACTIVATED;
    }
}
