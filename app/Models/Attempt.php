<?php

namespace App\Models;

use App\Enums\AttemptModeEnum;
use App\Enums\AttemptStatusEnum;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'user_id',
    'mode',
    'category_id',
    'subtest_id',
    'learning_module_id',
    'simulation_package_id',
    'status',
    'started_at',
    'submitted_at',
    'duration_seconds',
    'total_questions',
    'answered_questions',
    'correct_answers',
    'wrong_answers',
    'blank_answers',
    'score_total',
    'accuracy',
    'result_summary',
    'analysis_text',
])]
class Attempt extends Model
{
    protected function casts(): array
    {
        return [
            'mode' => AttemptModeEnum::class,
            'status' => AttemptStatusEnum::class,
            'started_at' => 'datetime',
            'submitted_at' => 'datetime',
            'score_total' => 'decimal:2',
            'accuracy' => 'decimal:2',
            'result_summary' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function subtest(): BelongsTo
    {
        return $this->belongsTo(Subtest::class);
    }

    public function learningModule(): BelongsTo
    {
        return $this->belongsTo(LearningModule::class);
    }

    public function simulationPackage(): BelongsTo
    {
        return $this->belongsTo(SimulationPackage::class);
    }

    public function attemptQuestions(): HasMany
    {
        return $this->hasMany(AttemptQuestion::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(AttemptAnswer::class);
    }
}
