<?php

namespace App\Models;

use App\Enums\ModuleLevelEnum;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'subtest_id',
    'title',
    'slug',
    'summary',
    'content',
    'tips',
    'tricks',
    'level',
    'estimated_minutes',
    'is_published',
    'published_at',
    'created_by',
    'updated_by',
])]
class LearningModule extends Model
{
    protected function casts(): array
    {
        return [
            'level' => ModuleLevelEnum::class,
            'is_published' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    public function subtest(): BelongsTo
    {
        return $this->belongsTo(Subtest::class);
    }

    public function progresses(): HasMany
    {
        return $this->hasMany(LearningModuleProgress::class);
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(Attempt::class);
    }
}
