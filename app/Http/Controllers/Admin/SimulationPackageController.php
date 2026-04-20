<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SaveSimulationPackageRequest;
use App\Models\SimulationPackage;
use App\Models\Subtest;
use App\Services\SimulationPackageManagementService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class SimulationPackageController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', SimulationPackage::class);

        $filters = [
            'q' => trim((string) $request->string('q')),
            'status' => (string) $request->string('status', 'all'),
        ];

        $packages = SimulationPackage::query()
            ->withCount('packageSubtests')
            ->when($filters['q'] !== '', function ($query) use ($filters) {
                $query->where(function ($innerQuery) use ($filters) {
                    $innerQuery
                        ->where('title', 'like', '%'.$filters['q'].'%')
                        ->orWhere('slug', 'like', '%'.$filters['q'].'%')
                        ->orWhere('description', 'like', '%'.$filters['q'].'%');
                });
            })
            ->when($filters['status'] === 'published', fn ($query) => $query->where('is_published', true))
            ->when($filters['status'] === 'draft', fn ($query) => $query->where('is_published', false))
            ->orderBy('sort_order')
            ->orderBy('title')
            ->paginate(10)
            ->withQueryString()
            ->through(fn (SimulationPackage $simulationPackage): array => [
                'id' => $simulationPackage->id,
                'title' => $simulationPackage->title,
                'slug' => $simulationPackage->slug,
                'description' => Str::limit((string) $simulationPackage->description, 120),
                'duration_minutes' => $simulationPackage->duration_minutes,
                'question_count' => $simulationPackage->question_count,
                'subtests_count' => $simulationPackage->package_subtests_count,
                'sort_order' => $simulationPackage->sort_order,
                'is_published' => $simulationPackage->is_published,
                'updated_at' => $simulationPackage->updated_at?->toDateTimeString(),
            ]);

        return Inertia::render('Admin/SimulationPackages/Index', [
            'packages' => $packages,
            'filters' => $filters,
            'stats' => [
                'total' => SimulationPackage::query()->count(),
                'published' => SimulationPackage::query()->where('is_published', true)->count(),
                'draft' => SimulationPackage::query()->where('is_published', false)->count(),
            ],
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', SimulationPackage::class);

        return Inertia::render('Admin/SimulationPackages/Create', [
            'simulationPackage' => null,
            'subtests' => $this->subtestOptions(),
        ]);
    }

    public function store(
        SaveSimulationPackageRequest $request,
        SimulationPackageManagementService $service,
    ): RedirectResponse {
        $simulationPackage = $service->create($request->payload(), $request->user());

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Paket simulasi berhasil dibuat.',
        ]);

        return to_route('admin.simulation-packages.edit', $simulationPackage);
    }

    public function edit(SimulationPackage $simulationPackage): Response
    {
        $this->authorize('update', $simulationPackage);

        $simulationPackage->load('packageSubtests');

        return Inertia::render('Admin/SimulationPackages/Edit', [
            'simulationPackage' => [
                'id' => $simulationPackage->id,
                'title' => $simulationPackage->title,
                'slug' => $simulationPackage->slug,
                'description' => $simulationPackage->description,
                'instruction' => $simulationPackage->instruction,
                'duration_minutes' => $simulationPackage->duration_minutes,
                'sort_order' => $simulationPackage->sort_order,
                'is_published' => $simulationPackage->is_published,
                'subtests' => $simulationPackage->packageSubtests
                    ->sortBy('sort_order')
                    ->values()
                    ->map(fn ($row): array => [
                        'subtest_id' => $row->subtest_id,
                        'question_count' => $row->question_count,
                        'sort_order' => $row->sort_order,
                    ])->all(),
            ],
            'subtests' => $this->subtestOptions(),
        ]);
    }

    public function update(
        SaveSimulationPackageRequest $request,
        SimulationPackage $simulationPackage,
        SimulationPackageManagementService $service,
    ): RedirectResponse {
        $service->update($simulationPackage, $request->payload(), $request->user());

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Paket simulasi berhasil diperbarui.',
        ]);

        return to_route('admin.simulation-packages.edit', $simulationPackage);
    }

    public function destroy(
        SimulationPackage $simulationPackage,
        SimulationPackageManagementService $service,
    ): RedirectResponse {
        $this->authorize('delete', $simulationPackage);

        $service->delete($simulationPackage);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Paket simulasi berhasil dihapus.',
        ]);

        return to_route('admin.simulation-packages.index');
    }

    public function togglePublication(
        SimulationPackage $simulationPackage,
        SimulationPackageManagementService $service,
    ): RedirectResponse {
        $this->authorize('togglePublication', $simulationPackage);

        $simulationPackage = $service->togglePublication($simulationPackage);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => $simulationPackage->is_published
                ? 'Paket simulasi dipublish.'
                : 'Paket simulasi di-unpublish.',
        ]);

        return back();
    }

    protected function subtestOptions(): array
    {
        return Subtest::query()
            ->with('category:id,name')
            ->where('is_active', true)
            ->orderBy('category_id')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->map(fn (Subtest $subtest): array => [
                'id' => $subtest->id,
                'name' => $subtest->name,
                'category' => $subtest->category?->name,
            ])
            ->all();
    }
}
