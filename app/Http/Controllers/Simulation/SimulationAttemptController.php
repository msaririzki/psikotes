<?php

namespace App\Http\Controllers\Simulation;

use App\Enums\AttemptStatusEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Simulation\StartSimulationRequest;
use App\Http\Requests\Simulation\SubmitSimulationRequest;
use App\Models\Attempt;
use App\Models\SimulationPackage;
use App\Services\SimulationSessionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class SimulationAttemptController extends Controller
{
    public function store(
        StartSimulationRequest $request,
        SimulationPackage $simulationPackage,
        SimulationSessionService $simulationSessionService,
    ): RedirectResponse {
        $attempt = $simulationSessionService->start($request->user(), $simulationPackage);

        if ($attempt->status === AttemptStatusEnum::SUBMITTED) {
            return to_route('simulations.attempts.result', $attempt);
        }

        return to_route('simulations.attempts.show', $attempt);
    }

    public function show(Attempt $attempt, SimulationSessionService $simulationSessionService): Response|RedirectResponse
    {
        Gate::authorize('viewSimulation', $attempt);

        $payload = $simulationSessionService->serializeSession($attempt);

        if ($payload['submitted'] ?? false) {
            return to_route('simulations.attempts.result', $attempt);
        }

        return Inertia::render('Simulations/Attempts/Show', [
            'attempt' => $payload,
        ]);
    }

    public function submit(
        SubmitSimulationRequest $request,
        Attempt $attempt,
        SimulationSessionService $simulationSessionService,
    ): RedirectResponse {
        $attempt = $simulationSessionService->submit(
            $attempt,
            $request->payload()['answers'],
            $request->payload()['flags'],
        );

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Simulasi berhasil dikirim.',
        ]);

        return to_route('simulations.attempts.result', $attempt);
    }
}
