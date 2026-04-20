<?php

namespace App\Models;

use App\Enums\ScoringTypeEnum;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'category_id',
    'name',
    'slug',
    'description',
    'instruction',
    'scoring_type',
    'default_duration_minutes',
    'sort_order',
    'is_active',
])]
class Subtest extends Model
{
    protected function casts(): array
    {
        return [
            'scoring_type' => ScoringTypeEnum::class,
            'is_active' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function learningModules(): HasMany
    {
        return $this->hasMany(LearningModule::class);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class);
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(Attempt::class);
    }

    public function simulationPackageSubtests(): HasMany
    {
        return $this->hasMany(SimulationPackageSubtest::class);
    }

    public function simulationPackages(): BelongsToMany
    {
        return $this->belongsToMany(SimulationPackage::class, 'simulation_package_subtests')
            ->withPivot(['id', 'question_count', 'sort_order'])
            ->withTimestamps();
    }

    public function activePublishedLearningModules(): HasMany
    {
        return $this->learningModules()->where('is_published', true);
    }
}
