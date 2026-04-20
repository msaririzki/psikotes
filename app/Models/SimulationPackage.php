<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'title',
    'slug',
    'description',
    'instruction',
    'duration_minutes',
    'question_count',
    'sort_order',
    'is_published',
    'published_at',
    'created_by',
    'updated_by',
])]
class SimulationPackage extends Model
{
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    public function packageSubtests(): HasMany
    {
        return $this->hasMany(SimulationPackageSubtest::class);
    }

    public function subtests(): BelongsToMany
    {
        return $this->belongsToMany(Subtest::class, 'simulation_package_subtests')
            ->withPivot(['id', 'question_count', 'sort_order'])
            ->withTimestamps()
            ->orderByPivot('sort_order');
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(Attempt::class);
    }
}
