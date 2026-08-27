<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class School extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_REJECTED = 'rejected';

    public const STATUS_SUSPENDED = 'suspended';

    protected $fillable = [
        'code',
        'name',
        'address',
        'phone',
        'email',
        'website',
        'status',
        'admin_name',
        'admin_email',
        'admin_phone',
        'admin_password',
        'rejection_reason',
        'approved_at',
        'approved_by',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
        'admin_password' => 'hashed',
    ];

    protected $hidden = [
        'admin_password',
    ];

    public function settings(): HasOne
    {
        return $this->hasOne(SchoolSetting::class);
    }

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isSuspended(): bool
    {
        return $this->status === self::STATUS_SUSPENDED;
    }

    public function displayLabel(): string
    {
        return trim($this->name.($this->code ? ' ('.$this->code.')' : ''));
    }
}
