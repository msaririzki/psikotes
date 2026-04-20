<?php

namespace App\Services;

use App\Models\Category;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CategoryManagementService
{
    public function __construct(
        protected UniqueSlugService $slugService,
    ) {}

    public function create(array $payload, User $actor): Category
    {
        return DB::transaction(function () use ($payload): Category {
            $payload['slug'] = $this->slugService->generate(Category::class, $payload['slug'] ?: $payload['name']);

            return Category::query()->create($payload);
        });
    }

    public function update(Category $category, array $payload, User $actor): Category
    {
        return DB::transaction(function () use ($category, $payload): Category {
            $payload['slug'] = $this->slugService->generate(
                Category::class,
                $payload['slug'] ?: $payload['name'],
                ignoreId: $category->id,
            );

            $category->fill($payload)->save();

            return $category->refresh();
        });
    }

    public function toggleActivity(Category $category): Category
    {
        $category->forceFill([
            'is_active' => ! $category->is_active,
        ])->save();

        return $category->refresh();
    }

    public function delete(Category $category): void
    {
        if ($category->subtests()->exists() || $category->questions()->exists()) {
            throw ValidationException::withMessages([
                'category' => 'Kategori tidak bisa dihapus karena masih memiliki subtes atau soal terkait.',
            ]);
        }

        $category->delete();
    }
}
