<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SaveCategoryRequest;
use App\Models\Category;
use App\Services\CategoryManagementService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class CategoryController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Category::class);

        $filters = [
            'q' => trim((string) $request->string('q')),
            'status' => (string) $request->string('status', 'all'),
        ];

        $categories = Category::query()
            ->withCount(['subtests', 'questions'])
            ->when($filters['q'] !== '', function ($query) use ($filters) {
                $query->where(function ($innerQuery) use ($filters) {
                    $innerQuery
                        ->where('name', 'like', '%'.$filters['q'].'%')
                        ->orWhere('slug', 'like', '%'.$filters['q'].'%')
                        ->orWhere('description', 'like', '%'.$filters['q'].'%');
                });
            })
            ->when($filters['status'] === 'active', fn ($query) => $query->where('is_active', true))
            ->when($filters['status'] === 'inactive', fn ($query) => $query->where('is_active', false))
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString()
            ->through(fn (Category $category): array => [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
                'description' => Str::limit((string) $category->description, 120),
                'sort_order' => $category->sort_order,
                'is_active' => $category->is_active,
                'subtests_count' => $category->subtests_count,
                'questions_count' => $category->questions_count,
                'updated_at' => $category->updated_at?->toDateTimeString(),
            ]);

        return Inertia::render('Admin/Categories/Index', [
            'categories' => $categories,
            'filters' => $filters,
            'stats' => [
                'total' => Category::query()->count(),
                'active' => Category::query()->where('is_active', true)->count(),
                'inactive' => Category::query()->where('is_active', false)->count(),
            ],
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Category::class);

        return Inertia::render('Admin/Categories/Create', [
            'category' => null,
        ]);
    }

    public function store(
        SaveCategoryRequest $request,
        CategoryManagementService $service,
    ): RedirectResponse {
        $service->create($request->payload(), $request->user());

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Kategori berhasil dibuat.',
        ]);

        return to_route('admin.categories.index');
    }

    public function edit(Category $category): Response
    {
        $this->authorize('update', $category);

        return Inertia::render('Admin/Categories/Edit', [
            'category' => [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
                'description' => $category->description,
                'sort_order' => $category->sort_order,
                'is_active' => $category->is_active,
            ],
        ]);
    }

    public function update(
        SaveCategoryRequest $request,
        Category $category,
        CategoryManagementService $service,
    ): RedirectResponse {
        $service->update($category, $request->payload(), $request->user());

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Kategori berhasil diperbarui.',
        ]);

        return to_route('admin.categories.index');
    }

    public function destroy(
        Category $category,
        CategoryManagementService $service,
    ): RedirectResponse {
        $this->authorize('delete', $category);

        $service->delete($category);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Kategori berhasil dihapus.',
        ]);

        return to_route('admin.categories.index');
    }

    public function toggleActivity(
        Category $category,
        CategoryManagementService $service,
    ): RedirectResponse {
        $this->authorize('toggleActivity', $category);

        $category = $service->toggleActivity($category);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => $category->is_active
                ? 'Kategori diaktifkan.'
                : 'Kategori dinonaktifkan.',
        ]);

        return back();
    }
}
