<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;

class AssessmentType extends Model
{
    use BelongsToSchool;

    public const CATEGORY_CLASS = 'class_assessment';

    public const CATEGORY_EXAMINATION = 'examination_assessment';

    public const CATEGORIES = [
        self::CATEGORY_CLASS => 'Class Assessment',
        self::CATEGORY_EXAMINATION => 'Examination Assessment',
    ];

    protected $fillable = [
        'school_id',
        'name',
        'slug',
        'category',
        'sort_order',
        'max_number',
        'total_score',
        'status',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'max_number' => 'integer',
        'total_score' => 'decimal:2',
    ];

    public function assessments()
    {
        return $this->hasMany(AcademicAssessment::class, 'type', 'slug');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'Active');
    }

    public function categoryLabel(): string
    {
        return self::CATEGORIES[$this->category] ?? ucwords(str_replace('_', ' ', (string) $this->category));
    }

    public static function categoryOptions(): array
    {
        return self::CATEGORIES;
    }

    public static function activeSlugs(): array
    {
        return static::query()
            ->active()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->pluck('slug')
            ->all();
    }

    public function isInUse(): bool
    {
        if ($this->relationLoaded('assessments')) {
            return $this->assessments->isNotEmpty();
        }

        if (isset($this->assessments_count)) {
            return (int) $this->assessments_count > 0;
        }

        return $this->assessments()->exists();
    }
}
