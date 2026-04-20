<?php

namespace App\Services;

use App\Enums\LearningModuleProgressStatusEnum;
use App\Models\Attempt;
use App\Models\LearningModule;
use App\Models\LearningModuleProgress;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class LearningProgressService
{
    public function __construct(
        protected StudyPlanTaskAutoResolutionService $studyPlanTaskAutoResolutionService,
    ) {}

    public function touchForViewing(User $user, LearningModule $learningModule): LearningModuleProgress
    {
        $progress = DB::transaction(function () use ($user, $learningModule): LearningModuleProgress {
            $progress = LearningModuleProgress::query()->firstOrNew([
                'user_id' => $user->id,
                'learning_module_id' => $learningModule->id,
            ]);

            if (! $progress->exists) {
                $progress->started_at = now();
            }

            if ($progress->status !== LearningModuleProgressStatusEnum::COMPLETED) {
                $progress->status = LearningModuleProgressStatusEnum::IN_PROGRESS;
            }

            $progress->last_viewed_at = now();
            $progress->save();

            return $progress->refresh();
        });

        $this->studyPlanTaskAutoResolutionService->learningModuleViewed($user, $learningModule);

        return $progress;
    }

    public function markCompleted(User $user, LearningModule $learningModule): LearningModuleProgress
    {
        $progress = DB::transaction(function () use ($user, $learningModule): LearningModuleProgress {
            $progress = LearningModuleProgress::query()->firstOrNew([
                'user_id' => $user->id,
                'learning_module_id' => $learningModule->id,
            ]);

            $progress->forceFill([
                'status' => LearningModuleProgressStatusEnum::COMPLETED,
                'started_at' => $progress->started_at ?? now(),
                'completed_at' => now(),
                'last_viewed_at' => now(),
            ])->save();

            return $progress->refresh();
        });

        $this->studyPlanTaskAutoResolutionService->learningModuleCompleted($user, $learningModule);

        return $progress;
    }

    public function syncQuizCompletion(User $user, LearningModule $learningModule, Attempt $attempt): LearningModuleProgress
    {
        return DB::transaction(function () use ($user, $learningModule, $attempt): LearningModuleProgress {
            $progress = LearningModuleProgress::query()->firstOrNew([
                'user_id' => $user->id,
                'learning_module_id' => $learningModule->id,
            ]);

            $attemptCount = $progress->exists
                ? $progress->quiz_attempts_count + 1
                : 1;

            $progress->forceFill([
                'status' => $progress->status === LearningModuleProgressStatusEnum::COMPLETED
                    ? LearningModuleProgressStatusEnum::COMPLETED
                    : LearningModuleProgressStatusEnum::IN_PROGRESS,
                'started_at' => $progress->started_at ?? $attempt->started_at ?? now(),
                'last_viewed_at' => now(),
                'last_quiz_attempt_id' => $attempt->id,
                'last_quiz_score' => $attempt->score_total,
                'quiz_attempts_count' => $attemptCount,
            ])->save();

            return $progress->refresh();
        });
    }

    public function progressMapForUser(User $user, array $learningModuleIds): Collection
    {
        if ($learningModuleIds === []) {
            return collect();
        }

        return LearningModuleProgress::query()
            ->where('user_id', $user->id)
            ->whereIn('learning_module_id', $learningModuleIds)
            ->get()
            ->keyBy('learning_module_id');
    }

    public function serialize(?LearningModuleProgress $progress): array
    {
        if (! $progress) {
            return [
                'status' => 'not_started',
                'label' => 'Belum Mulai',
                'last_quiz_score' => null,
                'quiz_attempts_count' => 0,
                'completed_at' => null,
                'last_viewed_at' => null,
            ];
        }

        return [
            'status' => $progress->status?->value,
            'label' => $progress->status?->label(),
            'last_quiz_score' => $progress->last_quiz_score !== null
                ? (float) $progress->last_quiz_score
                : null,
            'quiz_attempts_count' => $progress->quiz_attempts_count,
            'completed_at' => $progress->completed_at?->toIso8601String(),
            'last_viewed_at' => $progress->last_viewed_at?->toIso8601String(),
        ];
    }
}
