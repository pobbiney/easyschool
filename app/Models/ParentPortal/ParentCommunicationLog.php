<?php

namespace App\Models\ParentPortal;

use App\Models\Student;
use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ParentCommunicationLog extends Model
{
    use BelongsToSchool;

    public const CHANNEL_SMS = 'sms';

    public const CHANNEL_PAYMENT = 'payment';

    protected $fillable = [
        'school_id',
        'parent_account_id',
        'student_id',
        'channel',
        'message',
        'reference_type',
        'reference_id',
        'sent_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
    ];

    public function parentAccount(): BelongsTo
    {
        return $this->belongsTo(ParentAccount::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
