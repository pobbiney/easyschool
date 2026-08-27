<?php

namespace App\Models\ParentPortal;

use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;

class ParentAccount extends Authenticatable
{
    use BelongsToSchool;

    public const STATUS_ACTIVE = 'Active';

    protected $fillable = [
        'school_id',
        'phone',
        'guardian_name',
        'password',
        'status',
        'must_change_password',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'must_change_password' => 'boolean',
        'last_login_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function messages(): HasMany
    {
        return $this->hasMany(ParentMessage::class);
    }

    public function communicationLogs(): HasMany
    {
        return $this->hasMany(ParentCommunicationLog::class);
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }
}
