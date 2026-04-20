<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ModuleLevelEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SaveLearningModuleRequest;
use App\Models\Category;
use App\Models\LearningModule;
use App\Models\Subtest;
use App\Services\LearningModuleManagementService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class LearningModuleController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', LearningModule::class);

        $filters = [
            'q' => trim((string) $request->string('q')),
            'category_id' => $request->integer('category_id') ?: null,
            'subtest_id' => $request->integer('subtest_id') ?: null,
            'level' => (string) $request->string('level', 'all'),
            'publication' => (string) $request->string('publication', 'all'),
        ];

        $modules = LearningModule::query()
            ->with(['subtest:id,category_id,name', 'subtest.category:id,name'])
            ->when($filters['q'] !== '', function ($query) use ($filters) {
                $query->where(function ($innerQuery) use ($filters) {
                    $innerQuery
                        ->where('title', 'like', '%'.$filters['q'].'%')
                        ->orWhere('slug', 'like', '%'.$filters['q'].'%')
                        ->orWhere('summary', 'like', '%'.$filters['q'].'%');
                });
            })
            ->when($filters['category_id'], function ($query, $categoryId) {
                $query->whereHas('subtest', fn ($subtestQuery) => $subtestQuery->where('category_id', $categoryId));
            })
            ->when($filters['subtest_id'], fn ($query, $subtestId) => $query->where('subtest_id', $subtestId))
            ->when($filters['level'] !== 'all', fn ($query) => $query->where('level', $filters['level']))
            ->when($filters['publication'] === 'published', fn ($query) => $query->where('is_published', true))
            ->when($filters['publication'] === 'draft', fn ($query) => $query->where('is_published', false))
            ->latest('updated_at')
            ->paginate(10)
            ->withQueryString()
            ->through(fn (LearningModule $module): array => [
                'id' => $module->id,
                'title' => $module->title,
                'slug' => $module->slug,
                'summary' => Str::limit((string) $module->summary, 120),
                'level' => $module->level?->value,
                'level_label' => $module->level?->label(),
                'estimated_minutes' => $module->estimated_minutes,
                'is_published' => $module->is_published,
                'published_at' => $module->published_at?->toDateTimeString(),
                'subtest' => $module->subtest?->name,
                'subtest_id' => $module->subtest_id,
                'category' => $module->subtest?->category?->name,
                'updated_at' => $module->updated_at?->toDateTimeString(),
            ]);

        return Inertia::render('Admin/LearningModules/Index', [
            'modules' => $modules,
            'filters' => $filters,
            'stats' => [
                'total' => LearningModule::query()->count(),
                'published' => LearningModule::query()->where('is_published', true)->count(),
                'draft' => LearningModule::query()->where('is_published', false)->count(),
            ],
            'categories' => $this->categories(),
            'subtests' => $this->subtests(),
            'levels' => $this->levels(),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', LearningModule::class);

        return Inertia::render('Admin/LearningModules/Create', [
            'learningModule' => null,
            'categories' => $this->categories(),
            'subtests' => $this->subtests(),
            'levels' => $this->levels(),
        ]);
    }

    public function store(
        SaveLearningModuleRequest $request,
        LearningModuleManagementService $service,
    ): RedirectResponse {
        $service->create($request->payload(), $request->user());

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Modul belajar berhasil dibuat.',
        ]);

        return to_route('admin.learning-modules.index');
    }

    public function edit(LearningModule $learningModule): Response
    {
        $this->authorize('update', $learningModule);

        $learningModule->load('subtest:id,category_id');

        return Inertia::render('Admin/LearningModules/Edit', [
            'learningModule' => [
                'id' => $learningModule->id,
                'category_id' => $learningModule->subtest?->category_id,
                'subtest_id' => $learningModule->subtest_id,
                'title' => $learningModule->title,
                'slug' => $learningModule->slug,
                'summary' => $learningModule->summary,
                'content' => $learningModule->content,
                'tips' => $learningModule->tips,
                'tricks' => $learningModule->tricks,
                'level' => $learningModule->level?->value,
                'estimated_minutes' => $learningModule->estimated_minutes,
                'is_published' => $learningModule->is_published,
            ],
            'categories' => $this->categories(),
            'subtests' => $this->subtests(),
            'levels' => $this->levels(),
        ]);
    }

    public function update(
        SaveLearningModuleRequest $request,
        LearningModule $learningModule,
        LearningModuleManagementService $service,
    ): RedirectResponse {
        $service->update($learningModule, $request->payload(), $request->user());

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Modul belajar berhasil diperbarui.',
        ]);

        return to_route('admin.learning-modules.index');
    }

    public function destroy(
        LearningModule $learningModule,
        LearningModuleManagementService $service,
    ): RedirectResponse {
        $this->authorize('delete', $learningModule);

        $service->delete($learningModule);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Modul belajar berhasil dihapus.',
        ]);

        return to_route('admin.learning-modules.index');
    }

    public function togglePublication(
        LearningModule $learningModule,
        LearningModuleManagementService $service,
        Request $request,
    ): RedirectResponse {
        $this->authorize('togglePublication', $learningModule);

        $learningModule = $service->togglePublication($learningModule, $request->user());

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => $learningModule->is_published
                ? 'Modul berhasil dipublish.'
                : 'Publikasi modul dibatalkan.',
        ]);

        return back();
    }

    private function categories(): array
    {
        return Category::query()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn (Category $category): array => [
                'id' => $category->id,
                'name' => $category->name,
            ])
            ->all();
    }

    private function subtests(): array
    {
        return Subtest::query()
            ->orderBy('category_id')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'category_id', 'name'])
            ->map(fn (Subtest $subtest): array => [
                'id' => $subtest->id,
                'category_id' => $subtest->category_id,
                'name' => $subtest->name,
            ])
            ->all();
    }

    private function levels(): array
    {
        return collect(ModuleLevelEnum::cases())
            ->map(fn (ModuleLevelEnum $level): array => [
                'value' => $level->value,
                'label' => $level->label(),
            ])
            ->all();
    }
}
