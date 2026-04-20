<?php

namespace App\Models;

use App\Enums\StudyGoalPeriodEnum;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id',
    'goal_key',
    'period_type',
    'goal_type',
    'title',
    'description',
    'rationale',
    'period_starts_on',
    'period_ends_on',
    'focus_payload',
    'target_payload',
    'baseline_payload',
    'metadata',
    'is_active',
    'last_generated_at',
])]
class StudyGoal extends Model
{
    protected function casts(): array
    {
        return [
            'period_type' => StudyGoalPeriodEnum::class,
            'period_starts_on' => 'date',
            'period_ends_on' => 'date',
            'focus_payload' => 'array',
            'target_payload' => 'array',
            'baseline_payload' => 'array',
            'metadata' => 'array',
            'is_active' => 'boolean',
            'last_generated_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
