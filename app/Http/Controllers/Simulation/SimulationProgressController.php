<?php

namespace App\Http\Controllers\Simulation;

use App\Enums\AttemptStatusEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Simulation\SaveSimulationProgressRequest;
use App\Models\Attempt;
use App\Services\SimulationSessionService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

class SimulationProgressController extends Controller
{
    public function __invoke(
        SaveSimulationProgressRequest $request,
        Attempt $attempt,
        SimulationSessionService $simulationSessionService,
    ): RedirectResponse {
        $attempt = $simulationSessionService->saveProgress(
            $attempt,
            $request->payload()['answers'],
            $request->payload()['flags'],
        );

        if ($attempt->status === AttemptStatusEnum::SUBMITTED) {
            Inertia::flash('toast', [
                'type' => 'success',
                'message' => 'Waktu simulasi habis dan attempt dikirim otomatis.',
            ]);

            return to_route('simulations.attempts.result', $attempt);
        }

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Progress simulasi berhasil disimpan.',
        ]);

        return back();
    }
}
