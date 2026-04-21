<?php

namespace App\Http\Controllers\Simulation;

use App\Enums\AttemptStatusEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Simulation\SaveSimulationProgressRequest;
use App\Models\Attempt;
use App\Services\SimulationSessionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

class SimulationProgressController extends Controller
{
    public function __invoke(
        SaveSimulationProgressRequest $request,
        Attempt $attempt,
        SimulationSessionService $simulationSessionService,
    ): JsonResponse|RedirectResponse {
        $payload = $request->payload();
        $attempt = $simulationSessionService->saveProgress(
            $attempt,
            $payload['answers'],
            $payload['flags'],
        );

        $isSilent = $payload['silent'];
        $message = $attempt->status === AttemptStatusEnum::SUBMITTED
            ? 'Waktu simulasi habis dan attempt dikirim otomatis.'
            : 'Progress simulasi berhasil disimpan.';

        if ($attempt->status === AttemptStatusEnum::SUBMITTED) {
            if ($request->expectsJson()) {
                return response()->json([
                    'submitted' => true,
                    'redirect_url' => route('simulations.attempts.result', $attempt),
                    'message' => $message,
                ]);
            }

            Inertia::flash('toast', [
                'type' => 'success',
                'message' => $message,
            ]);

            return to_route('simulations.attempts.result', $attempt);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'submitted' => false,
                'redirect_url' => null,
                'message' => $message,
            ]);
        }

        if (! $isSilent) {
            Inertia::flash('toast', [
                'type' => 'success',
                'message' => $message,
            ]);
        }

        return back();
    }
}
