<?php

namespace App\Models;

use App\Enums\StudyPlanTaskStatusEnum;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'user_id',
    'task_key',
    'status',
    'type',
    'track',
    'source',
    'title',
    'description',
    'reason',
    'action_label',
    'action_href',
    'priority_score',
    'priority_label',
    'recommended_for_date',
    'scheduled_for_date',
    'snoozed_until',
    'completed_at',
    'completion_source',
    'resolved_activity_type',
    'resolved_activity_id',
    'last_generated_at',
    'is_active',
    'metadata',
])]
class StudyPlanTask extends Model
{
    protected function casts(): array
    {
        return [
            'status' => StudyPlanTaskStatusEnum::class,
            'recommended_for_date' => 'date',
            'scheduled_for_date' => 'date',
            'snoozed_until' => 'date',
            'completed_at' => 'datetime',
            'resolved_activity_id' => 'integer',
            'last_generated_at' => 'datetime',
            'is_active' => 'boolean',
            'metadata' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(StudyPlanTaskEvent::class)->orderByDesc('happened_at');
    }
}
