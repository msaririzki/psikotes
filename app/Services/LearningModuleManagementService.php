<?php

namespace App\Services;

use App\Models\LearningModule;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class LearningModuleManagementService
{
    public function __construct(
        protected UniqueSlugService $slugService,
    ) {}

    public function create(array $payload, User $actor): LearningModule
    {
        return DB::transaction(function () use ($payload, $actor): LearningModule {
            $payload['slug'] = $this->slugService->generate(LearningModule::class, $payload['slug'] ?: $payload['title']);
            $payload['created_by'] = $actor->id;
            $payload['updated_by'] = $actor->id;
            $payload['published_at'] = $payload['is_published'] ? now() : null;

            return LearningModule::query()->create($payload);
        });
    }

    public function update(LearningModule $learningModule, array $payload, User $actor): LearningModule
    {
        return DB::transaction(function () use ($learningModule, $payload, $actor): LearningModule {
            $payload['slug'] = $this->slugService->generate(
                LearningModule::class,
                $payload['slug'] ?: $payload['title'],
                ignoreId: $learningModule->id,
            );
            $payload['updated_by'] = $actor->id;

            if ($payload['is_published'] && ! $learningModule->is_published) {
                $payload['published_at'] = now();
            }

            if (! $payload['is_published']) {
                $payload['published_at'] = null;
            }

            $learningModule->fill($payload)->save();

            return $learningModule->refresh();
        });
    }

    public function togglePublication(LearningModule $learningModule, User $actor): LearningModule
    {
        $learningModule->forceFill([
            'is_published' => ! $learningModule->is_published,
            'published_at' => ! $learningModule->is_published ? now() : null,
            'updated_by' => $actor->id,
        ])->save();

        return $learningModule->refresh();
    }

    public function delete(LearningModule $learningModule): void
    {
        $learningModule->delete();
    }
}
