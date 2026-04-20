<?php

namespace App\Models;

use App\Enums\LearningLevelEnum;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id',
    'education_level',
    'target_exam',
    'learning_level',
    'target_daily_minutes',
    'preferred_focus',
    'onboarding_answers',
])]
class UserProfile extends Model
{
    protected function casts(): array
    {
        return [
            'learning_level' => LearningLevelEnum::class,
            'onboarding_answers' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
