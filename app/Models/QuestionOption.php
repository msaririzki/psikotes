<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'question_id',
    'option_key',
    'option_text',
    'option_image',
    'weight',
    'is_correct',
    'sort_order',
])]
class QuestionOption extends Model
{
    protected function casts(): array
    {
        return [
            'weight' => 'decimal:2',
            'is_correct' => 'boolean',
        ];
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }

    public function selectedInAnswers(): HasMany
    {
        return $this->hasMany(AttemptAnswer::class, 'selected_option_id');
    }
}
