<?php

namespace App\Models;

use App\Enums\StudyPlanTaskEventTypeEnum;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'study_plan_task_id',
    'user_id',
    'event_type',
    'description',
    'event_payload',
    'happened_at',
])]
class StudyPlanTaskEvent extends Model
{
    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'event_type' => StudyPlanTaskEventTypeEnum::class,
            'event_payload' => 'array',
            'happened_at' => 'datetime',
        ];
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(StudyPlanTask::class, 'study_plan_task_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
