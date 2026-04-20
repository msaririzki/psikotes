<?php

namespace App\Http\Controllers\Simulation;

use App\Http\Controllers\Controller;
use App\Models\SimulationPackage;
use App\Services\SimulationCatalogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class SimulationPackageController extends Controller
{
    public function show(
        Request $request,
        SimulationPackage $simulationPackage,
        SimulationCatalogService $simulationCatalogService,
    ): Response {
        Gate::authorize('viewCatalog', $simulationPackage);

        return Inertia::render('Simulations/Show', $simulationCatalogService->packageDetail($simulationPackage, $request->user()));
    }
}
