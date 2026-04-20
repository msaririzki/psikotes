<?php

namespace App\Services;

use App\Enums\QuestionStatusEnum;
use App\Enums\QuestionTypeEnum;
use App\Models\Attempt;
use App\Models\Question;
use App\Models\SimulationPackage;
use App\Models\Subtest;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SimulationPackageManagementService
{
    public function __construct(
        protected UniqueSlugService $slugService,
    ) {}

    public function create(array $payload, User $actor): SimulationPackage
    {
        $this->assertSubtestsAreEligible($payload['subtests'], $payload['is_published']);

        return DB::transaction(function () use ($payload, $actor): SimulationPackage {
            $simulationPackage = SimulationPackage::query()->create([
                'title' => $payload['title'],
                'slug' => $this->slugService->generate(SimulationPackage::class, $payload['slug'] ?: $payload['title']),
                'description' => $payload['description'],
                'instruction' => $payload['instruction'],
                'duration_minutes' => $payload['duration_minutes'],
                'question_count' => collect($payload['subtests'])->sum('question_count'),
                'sort_order' => $payload['sort_order'],
                'is_published' => $payload['is_published'],
                'published_at' => $payload['is_published'] ? now() : null,
                'created_by' => $actor->id,
                'updated_by' => $actor->id,
            ]);

            $this->syncSubtests($simulationPackage, $payload['subtests']);

            return $simulationPackage->refresh();
        });
    }

    public function update(SimulationPackage $simulationPackage, array $payload, User $actor): SimulationPackage
    {
        $this->assertSubtestsAreEligible($payload['subtests'], $payload['is_published']);

        return DB::transaction(function () use ($simulationPackage, $payload, $actor): SimulationPackage {
            $wasPublished = $simulationPackage->is_published;

            $simulationPackage->forceFill([
                'title' => $payload['title'],
                'slug' => $this->slugService->generate(
                    SimulationPackage::class,
                    $payload['slug'] ?: $payload['title'],
                    ignoreId: $simulationPackage->id,
                ),
                'description' => $payload['description'],
                'instruction' => $payload['instruction'],
                'duration_minutes' => $payload['duration_minutes'],
                'question_count' => collect($payload['subtests'])->sum('question_count'),
                'sort_order' => $payload['sort_order'],
                'is_published' => $payload['is_published'],
                'published_at' => $payload['is_published']
                    ? ($wasPublished ? $simulationPackage->published_at : now())
                    : null,
                'updated_by' => $actor->id,
            ])->save();

            $this->syncSubtests($simulationPackage, $payload['subtests']);

            return $simulationPackage->refresh();
        });
    }

    public function togglePublication(SimulationPackage $simulationPackage): SimulationPackage
    {
        $this->assertSubtestsAreEligible(
            $simulationPackage->packageSubtests()
                ->orderBy('sort_order')
                ->get(['subtest_id', 'question_count', 'sort_order'])
                ->map(fn ($row): array => [
                    'subtest_id' => $row->subtest_id,
                    'question_count' => $row->question_count,
                    'sort_order' => $row->sort_order,
                ])
                ->all(),
            ! $simulationPackage->is_published,
        );

        $simulationPackage->forceFill([
            'is_published' => ! $simulationPackage->is_published,
            'published_at' => ! $simulationPackage->is_published ? now() : null,
        ])->save();

        return $simulationPackage->refresh();
    }

    public function delete(SimulationPackage $simulationPackage): void
    {
        if (Attempt::query()->where('simulation_package_id', $simulationPackage->id)->exists()) {
            throw ValidationException::withMessages([
                'simulation_package' => 'Paket simulasi tidak bisa dihapus karena sudah dipakai histori attempt.',
            ]);
        }

        $simulationPackage->delete();
    }

    protected function syncSubtests(SimulationPackage $simulationPackage, array $subtests): void
    {
        $simulationPackage->packageSubtests()->delete();

        $simulationPackage->packageSubtests()->createMany(
            collect($subtests)
                ->sortBy('sort_order')
                ->values()
                ->map(fn (array $row): array => [
                    'subtest_id' => $row['subtest_id'],
                    'question_count' => $row['question_count'],
                    'sort_order' => $row['sort_order'],
                ])
                ->all(),
        );
    }

    protected function assertSubtestsAreEligible(array $subtests, bool $mustBeReadyToPublish): void
    {
        $subtestIds = collect($subtests)
            ->pluck('subtest_id')
            ->map(fn ($id): int => (int) $id)
            ->values();

        $activeSubtests = Subtest::query()
            ->whereIn('id', $subtestIds)
            ->where('is_active', true)
            ->get()
            ->keyBy('id');

        $invalidSubtest = $subtestIds->first(
            fn (int $subtestId): bool => ! $activeSubtests->has($subtestId),
        );

        if ($invalidSubtest !== null) {
            throw ValidationException::withMessages([
                'subtests' => 'Paket simulasi hanya boleh memakai subtes yang aktif.',
            ]);
        }

        if (! $mustBeReadyToPublish) {
            return;
        }

        $insufficientSubtest = collect($subtests)->first(function (array $row) use ($activeSubtests): bool {
            return $this->eligibleQuestionsCount((int) $row['subtest_id']) < (int) $row['question_count'];
        });

        if (! $insufficientSubtest) {
            return;
        }

        $subtest = $activeSubtests->get((int) $insufficientSubtest['subtest_id']);

        throw ValidationException::withMessages([
            'subtests' => "Bank soal publish untuk {$subtest->name} belum cukup untuk jumlah soal yang diminta.",
        ]);
    }

    protected function eligibleQuestionsCount(int $subtestId): int
    {
        return Question::query()
            ->with('options')
            ->where('subtest_id', $subtestId)
            ->where('status', QuestionStatusEnum::PUBLISHED)
            ->whereIn('question_type', [
                QuestionTypeEnum::MULTIPLE_CHOICE_SINGLE,
                QuestionTypeEnum::MULTIPLE_CHOICE_IMAGE,
                QuestionTypeEnum::TRUE_FALSE,
            ])
            ->has('options', '>=', 2)
            ->whereHas('options', fn (Builder $query) => $query->where('is_correct', true))
            ->get()
            ->filter(fn (Question $question): bool => $question->canBeUsedForPractice())
            ->count();
    }
}
