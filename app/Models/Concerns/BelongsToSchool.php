<?php

namespace App\Models\Concerns;

use App\Support\TenantContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

trait BelongsToSchool
{
    public static function bootBelongsToSchool(): void
    {
        static::addGlobalScope('school', function (Builder $builder) {
            if (! TenantContext::shouldApplyScope()) {
                return;
            }

            $schoolId = TenantContext::schoolId();

            if ($schoolId) {
                $builder->where($builder->getModel()->getTable().'.school_id', $schoolId);
            }
        });

        static::creating(function (Model $model) {
            if (! empty($model->school_id)) {
                return;
            }

            if (TenantContext::shouldApplyScope()) {
                $model->school_id = TenantContext::schoolId();
            }
        });
    }

    public function school()
    {
        return $this->belongsTo(\App\Models\School::class);
    }
}
