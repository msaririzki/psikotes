<?php

namespace App\Services;

use App\Enums\AttemptModeEnum;
use App\Enums\AttemptStatusEnum;
use App\Models\Attempt;
use App\Models\LearningModuleProgress;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class HistoryTimelineService
{
    public function build(User $user, string $filter = 'all', int $perPage = 12, int $page = 1): array
    {
        $timeline = $this->timelineCollection($user)
            ->when($filter !== 'all', fn (Collection $items) => $items->where('kind', $filter))
            ->sortByDesc('occurred_at')
            ->values();

        $offset = max(($page - 1) * $perPage, 0);
        $paginator = new LengthAwarePaginator(
            $timeline->slice($offset, $perPage)->values()->all(),
            $timeline->count(),
            $perPage,
            $page,
            [
                'path' => request()->url(),
                'query' => request()->query(),
            ],
        );

        return [
            'summary' => $this->summary($user),
            'filters' => [
                'type' => $filter,
                'items' => [
                    ['value' => 'all', 'label' => 'Semua aktivitas'],
                    ['value' => 'learn', 'label' => 'Learn'],
                    ['value' => 'mini_quiz', 'label' => 'Mini Quiz'],
                    ['value' => 'practice', 'label' => 'Practice'],
                    ['value' => 'simulation', 'label' => 'Simulation'],
                ],
            ],
            'timeline' => $paginator->through(fn (array $item): array => $item),
        ];
    }

    protected function timelineCollection(User $user): Collection
    {
        $progresses = LearningModuleProgress::query()
            ->where('user_id', $user->id)
            ->with('learningModule.subtest.category')
            ->get()
            ->map(function (LearningModuleProgress $progress): array {
                $module = $progress->learningModule;

                return [
                    'id' => 'learn-'.$progress->id,
                    'kind' => 'learn',
                    'kind_label' => 'Learn',
                    'title' => $module?->title ?? 'Modul belajar',
                    'subtitle' => $module?->subtest?->name,
                    'category_name' => $module?->subtest?->category?->name,
                    'status_label' => $progress->status?->label() ?? 'Belum Mulai',
                    'metric_label' => $progress->last_quiz_score !== null
                        ? 'Skor mini quiz '.(float) $progress->last_quiz_score
                        : 'Progress materi',
                    'description' => $progress->completed_at
                        ? 'Modul ditandai selesai dan siap dipakai sebagai fondasi subtes.'
                        : 'Modul sudah dibuka dan masih bisa dilanjutkan kapan saja.',
                    'occurred_at' => ($progress->completed_at ?? $progress->last_viewed_at ?? $progress->started_at)?->toIso8601String(),
                    'href' => $module ? '/learn/modules/'.$module->slug : '/learn',
                ];
            });

        $attempts = Attempt::query()
            ->where('user_id', $user->id)
            ->where('status', AttemptStatusEnum::SUBMITTED)
            ->whereIn('mode', [
                AttemptModeEnum::MINI_QUIZ,
                AttemptModeEnum::PRACTICE,
                AttemptModeEnum::SIMULATION,
            ])
            ->with([
                'subtest.category',
                'learningModule.subtest.category',
                'simulationPackage',
            ])
            ->get()
            ->map(function (Attempt $attempt): array {
                return match ($attempt->mode) {
                    AttemptModeEnum::MINI_QUIZ => [
                        'id' => 'mini-quiz-'.$attempt->id,
                        'kind' => 'mini_quiz',
                        'kind_label' => 'Mini Quiz',
                        'title' => $attempt->learningModule?->title ?? 'Mini quiz',
                        'subtitle' => $attempt->subtest?->name,
                        'category_name' => $attempt->subtest?->category?->name,
                        'status_label' => 'Selesai',
                        'metric_label' => 'Skor '.(float) ($attempt->score_total ?? 0),
                        'description' => 'Mini quiz selesai dan hasilnya bisa direview kapan saja.',
                        'occurred_at' => $attempt->submitted_at?->toIso8601String(),
                        'href' => '/learn/mini-quizzes/'.$attempt->id.'/result',
                    ],
                    AttemptModeEnum::PRACTICE => [
                        'id' => 'practice-'.$attempt->id,
                        'kind' => 'practice',
                        'kind_label' => 'Practice',
                        'title' => $attempt->subtest?->name ?? 'Practice',
                        'subtitle' => 'Latihan terstruktur',
                        'category_name' => $attempt->subtest?->category?->name,
                        'status_label' => 'Submitted',
                        'metric_label' => 'Akurasi '.(float) ($attempt->accuracy ?? 0).'%',
                        'description' => 'Sesi practice selesai dengan review jawaban dan pembahasan.',
                        'occurred_at' => $attempt->submitted_at?->toIso8601String(),
                        'href' => '/practice/attempts/'.$attempt->id.'/result',
                    ],
                    AttemptModeEnum::SIMULATION => [
                        'id' => 'simulation-'.$attempt->id,
                        'kind' => 'simulation',
                        'kind_label' => 'Simulation',
                        'title' => data_get($attempt->result_summary, 'package_snapshot.title', $attempt->simulationPackage?->title ?? 'Simulation'),
                        'subtitle' => 'CAT full session',
                        'category_name' => null,
                        'status_label' => 'Submitted',
                        'metric_label' => 'Skor '.(float) ($attempt->score_total ?? 0),
                        'description' => 'Histori simulasi tetap stabil karena direview dari immutable snapshot.',
                        'occurred_at' => $attempt->submitted_at?->toIso8601String(),
                        'href' => '/simulations/attempts/'.$attempt->id.'/result',
                    ],
                };
            });

        return $progresses->merge($attempts)
            ->filter(fn (array $item): bool => filled($item['occurred_at']));
    }

    protected function summary(User $user): array
    {
        return [
            'learn' => LearningModuleProgress::query()->where('user_id', $user->id)->count(),
            'mini_quiz' => Attempt::query()->where('user_id', $user->id)->where('mode', AttemptModeEnum::MINI_QUIZ)->where('status', AttemptStatusEnum::SUBMITTED)->count(),
            'practice' => Attempt::query()->where('user_id', $user->id)->where('mode', AttemptModeEnum::PRACTICE)->where('status', AttemptStatusEnum::SUBMITTED)->count(),
            'simulation' => Attempt::query()->where('user_id', $user->id)->where('mode', AttemptModeEnum::SIMULATION)->where('status', AttemptStatusEnum::SUBMITTED)->count(),
        ];
    }
}
