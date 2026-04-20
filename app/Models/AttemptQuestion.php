<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['attempt_id', 'question_id', 'display_order', 'section_name', 'snapshot'])]
class AttemptQuestion extends Model
{
    protected function casts(): array
    {
        return [
            'snapshot' => 'array',
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

    public function snapshotQuestion(): array
    {
        return data_get($this->snapshot, 'question', []);
    }

    public function snapshotOptions(): array
    {
        return data_get($this->snapshot, 'options', []);
    }

    public function optionSnapshotById(?int $optionId): ?array
    {
        if ($optionId === null) {
            return null;
        }

        return collect($this->snapshotOptions())
            ->firstWhere('id', $optionId);
    }
}
