<?php

namespace App\Models;

use App\Enums\DifficultyEnum;
use App\Enums\QuestionStatusEnum;
use App\Enums\QuestionTypeEnum;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'category_id',
    'subtest_id',
    'code',
    'question_type',
    'difficulty',
    'question_text',
    'question_image',
    'extra_data',
    'explanation_text',
    'answer_key_text',
    'status',
    'source_reference',
    'created_by',
    'updated_by',
])]
class Question extends Model
{
    protected function casts(): array
    {
        return [
            'question_type' => QuestionTypeEnum::class,
            'difficulty' => DifficultyEnum::class,
            'status' => QuestionStatusEnum::class,
            'extra_data' => 'array',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function subtest(): BelongsTo
    {
        return $this->belongsTo(Subtest::class);
    }

    public function options(): HasMany
    {
        return $this->hasMany(QuestionOption::class);
    }

    public function attemptQuestions(): HasMany
    {
        return $this->hasMany(AttemptQuestion::class);
    }

    public function attemptAnswers(): HasMany
    {
        return $this->hasMany(AttemptAnswer::class);
    }

    public function canBeUsedForMiniQuiz(): bool
    {
        return $this->canBeUsedForPractice();
    }

    public function canBeUsedForPractice(): bool
    {
        $options = $this->relationLoaded('options')
            ? $this->options
            : $this->options()->get();

        return $this->status === QuestionStatusEnum::PUBLISHED
            && $this->question_type?->supportsPractice()
            && $options->count() >= 2
            && $options->contains(fn ($option) => (bool) $option->is_correct);
    }
}
