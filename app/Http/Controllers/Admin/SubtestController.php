<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ScoringTypeEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SaveSubtestRequest;
use App\Models\Category;
use App\Models\Subtest;
use App\Services\SubtestManagementService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class SubtestController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Subtest::class);

        $filters = [
            'q' => trim((string) $request->string('q')),
            'category_id' => $request->integer('category_id') ?: null,
            'scoring_type' => (string) $request->string('scoring_type', 'all'),
            'status' => (string) $request->string('status', 'all'),
        ];

        $subtests = Subtest::query()
            ->with('category:id,name')
            ->withCount(['learningModules', 'questions'])
            ->when($filters['q'] !== '', function ($query) use ($filters) {
                $query->where(function ($innerQuery) use ($filters) {
                    $innerQuery
                        ->where('name', 'like', '%'.$filters['q'].'%')
                        ->orWhere('slug', 'like', '%'.$filters['q'].'%')
                        ->orWhere('description', 'like', '%'.$filters['q'].'%');
                });
            })
            ->when($filters['category_id'], fn ($query, $categoryId) => $query->where('category_id', $categoryId))
            ->when($filters['scoring_type'] !== 'all', fn ($query) => $query->where('scoring_type', $filters['scoring_type']))
            ->when($filters['status'] === 'active', fn ($query) => $query->where('is_active', true))
            ->when($filters['status'] === 'inactive', fn ($query) => $query->where('is_active', false))
            ->orderBy('category_id')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString()
            ->through(fn (Subtest $subtest): array => [
                'id' => $subtest->id,
                'name' => $subtest->name,
                'slug' => $subtest->slug,
                'description' => Str::limit((string) $subtest->description, 110),
                'category' => $subtest->category?->name,
                'category_id' => $subtest->category_id,
                'scoring_type' => $subtest->scoring_type?->value,
                'scoring_type_label' => $subtest->scoring_type?->label(),
                'default_duration_minutes' => $subtest->default_duration_minutes,
                'sort_order' => $subtest->sort_order,
                'is_active' => $subtest->is_active,
                'learning_modules_count' => $subtest->learning_modules_count,
                'questions_count' => $subtest->questions_count,
                'updated_at' => $subtest->updated_at?->toDateTimeString(),
            ]);

        return Inertia::render('Admin/Subtests/Index', [
            'subtests' => $subtests,
            'filters' => $filters,
            'stats' => [
                'total' => Subtest::query()->count(),
                'active' => Subtest::query()->where('is_active', true)->count(),
                'objective' => Subtest::query()->where('scoring_type', ScoringTypeEnum::OBJECTIVE)->count(),
            ],
            'categories' => Category::query()
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(['id', 'name'])
                ->map(fn (Category $category): array => [
                    'id' => $category->id,
                    'name' => $category->name,
                ]),
            'scoringTypes' => collect(ScoringTypeEnum::cases())->map(fn (ScoringTypeEnum $type): array => [
                'value' => $type->value,
                'label' => $type->label(),
            ]),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Subtest::class);

        return Inertia::render('Admin/Subtests/Create', [
            'subtest' => null,
            'categories' => $this->categories(),
            'scoringTypes' => $this->scoringTypes(),
        ]);
    }

    public function store(
        SaveSubtestRequest $request,
        SubtestManagementService $service,
    ): RedirectResponse {
        $service->create($request->payload(), $request->user());

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Subtes berhasil dibuat.',
        ]);

        return to_route('admin.subtests.index');
    }

    public function edit(Subtest $subtest): Response
    {
        $this->authorize('update', $subtest);

        return Inertia::render('Admin/Subtests/Edit', [
            'subtest' => [
                'id' => $subtest->id,
                'category_id' => $subtest->category_id,
                'name' => $subtest->name,
                'slug' => $subtest->slug,
                'description' => $subtest->description,
                'instruction' => $subtest->instruction,
                'scoring_type' => $subtest->scoring_type?->value,
                'default_duration_minutes' => $subtest->default_duration_minutes,
                'sort_order' => $subtest->sort_order,
                'is_active' => $subtest->is_active,
            ],
            'categories' => $this->categories(),
            'scoringTypes' => $this->scoringTypes(),
        ]);
    }

    public function update(
        SaveSubtestRequest $request,
        Subtest $subtest,
        SubtestManagementService $service,
    ): RedirectResponse {
        $service->update($subtest, $request->payload(), $request->user());

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Subtes berhasil diperbarui.',
        ]);

        return to_route('admin.subtests.index');
    }

    public function destroy(
        Subtest $subtest,
        SubtestManagementService $service,
    ): RedirectResponse {
        $this->authorize('delete', $subtest);

        $service->delete($subtest);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Subtes berhasil dihapus.',
        ]);

        return to_route('admin.subtests.index');
    }

    public function toggleActivity(
        Subtest $subtest,
        SubtestManagementService $service,
    ): RedirectResponse {
        $this->authorize('toggleActivity', $subtest);

        $subtest = $service->toggleActivity($subtest);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => $subtest->is_active
                ? 'Subtes diaktifkan.'
                : 'Subtes dinonaktifkan.',
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

    private function scoringTypes(): array
    {
        return collect(ScoringTypeEnum::cases())
            ->map(fn (ScoringTypeEnum $type): array => [
                'value' => $type->value,
                'label' => $type->label(),
            ])
            ->all();
    }
}
