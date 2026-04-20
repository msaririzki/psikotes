<?php

namespace App\Models;

use App\Enums\LearningModuleProgressStatusEnum;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id',
    'learning_module_id',
    'status',
    'started_at',
    'completed_at',
    'last_viewed_at',
    'last_quiz_attempt_id',
    'last_quiz_score',
    'quiz_attempts_count',
])]
class LearningModuleProgress extends Model
{
    protected function casts(): array
    {
        return [
            'status' => LearningModuleProgressStatusEnum::class,
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'last_viewed_at' => 'datetime',
            'last_quiz_score' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function learningModule(): BelongsTo
    {
        return $this->belongsTo(LearningModule::class);
    }

    public function lastQuizAttempt(): BelongsTo
    {
        return $this->belongsTo(Attempt::class, 'last_quiz_attempt_id');
    }
}
