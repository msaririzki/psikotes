<?php

namespace App\Http\Controllers\Simulation;

use App\Http\Controllers\Controller;
use App\Models\Attempt;
use App\Services\SimulationSessionService;
use App\Services\StudyPlanTaskAutoResolutionService;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class SimulationResultController extends Controller
{
    public function __invoke(
        Attempt $attempt,
        SimulationSessionService $simulationSessionService,
        StudyPlanTaskAutoResolutionService $studyPlanTaskAutoResolutionService,
    ): Response
    {
        Gate::authorize('viewSimulationResult', $attempt);

        $studyPlanTaskAutoResolutionService->simulationResultViewed($attempt);

        return Inertia::render('Simulations/Attempts/Result', $simulationSessionService->resultPayload($attempt));
    }
}
