<?php

namespace App\Http\Controllers\Learn;

use App\Http\Controllers\Controller;
use App\Http\Requests\Learn\CompleteLearningModuleRequest;
use App\Models\LearningModule;
use App\Services\LearningProgressService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

class CompleteLearningModuleController extends Controller
{
    public function __invoke(
        CompleteLearningModuleRequest $request,
        LearningModule $learningModule,
        LearningProgressService $progressService,
    ): RedirectResponse {
        $progressService->markCompleted($request->user(), $learningModule);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Modul ditandai selesai.',
        ]);

        return back();
    }
}
