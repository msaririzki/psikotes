<?php

namespace App\Http\Controllers\Simulation;

use App\Http\Controllers\Controller;
use App\Services\SimulationCatalogService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SimulationController extends Controller
{
    public function __invoke(Request $request, SimulationCatalogService $simulationCatalogService): Response
    {
        return Inertia::render('Simulations/Index', $simulationCatalogService->overview($request->user()));
    }
}
