<?php

namespace App\Services;

use App\Models\Attempt;
use App\Models\Subtest;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SubtestManagementService
{
    public function __construct(
        protected UniqueSlugService $slugService,
    ) {}

    public function create(array $payload, User $actor): Subtest
    {
        return DB::transaction(function () use ($payload): Subtest {
            $payload['slug'] = $this->slugService->generate(Subtest::class, $payload['slug'] ?: $payload['name']);

            return Subtest::query()->create($payload);
        });
    }

    public function update(Subtest $subtest, array $payload, User $actor): Subtest
    {
        return DB::transaction(function () use ($subtest, $payload): Subtest {
            $payload['slug'] = $this->slugService->generate(
                Subtest::class,
                $payload['slug'] ?: $payload['name'],
                ignoreId: $subtest->id,
            );

            $subtest->fill($payload)->save();

            return $subtest->refresh();
        });
    }

    public function toggleActivity(Subtest $subtest): Subtest
    {
        $subtest->forceFill([
            'is_active' => ! $subtest->is_active,
        ])->save();

        return $subtest->refresh();
    }

    public function delete(Subtest $subtest): void
    {
        if ($subtest->learningModules()->exists() || $subtest->questions()->exists() || Attempt::query()->where('subtest_id', $subtest->id)->exists()) {
            throw ValidationException::withMessages([
                'subtest' => 'Subtes tidak bisa dihapus karena masih dipakai modul, soal, atau histori attempt.',
            ]);
        }

        $subtest->delete();
    }
}
