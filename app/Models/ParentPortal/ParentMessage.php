<?php

namespace App\Models\ParentPortal;

use App\Models\Student;
use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ParentMessage extends Model
{
    use BelongsToSchool;

    public const STATUS_NEW = 'new';

    public const STATUS_READ = 'read';

    public const STATUS_REPLIED = 'replied';

    protected $fillable = [
        'school_id',
        'parent_account_id',
        'student_id',
        'message',
        'status',
        'admin_reply',
        'read_at',
    ];

    protected $casts = [
        'read_at' => 'datetime',
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
