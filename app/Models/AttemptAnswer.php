<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'attempt_id',
    'question_id',
    'selected_option_id',
    'answer_text',
    'answer_json',
    'is_flagged',
    'is_correct',
    'score',
    'time_spent_seconds',
    'answered_at',
])]
class AttemptAnswer extends Model
{
    protected function casts(): array
    {
        return [
            'answer_json' => 'array',
            'is_flagged' => 'boolean',
            'is_correct' => 'boolean',
            'score' => 'decimal:2',
            'answered_at' => 'datetime',
        ];
    }

    public function attempt(): BelongsTo
    {
        return $this->belongsTo(Attempt::class);
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }

    public function selectedOption(): BelongsTo
    {
        return $this->belongsTo(QuestionOption::class, 'selected_option_id');
    }

    public function selectedOptionSnapshot(): ?array
    {
        return data_get($this->answer_json, 'selected_option');
    }
}
