<?php

namespace App\Http\Controllers\Learn;

use App\Http\Controllers\Controller;
use App\Http\Requests\Learn\StartMiniQuizRequest;
use App\Http\Requests\Learn\SubmitMiniQuizRequest;
use App\Models\Attempt;
use App\Models\LearningModule;
use App\Services\MiniQuizService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class MiniQuizController extends Controller
{
    public function store(
        StartMiniQuizRequest $request,
        LearningModule $learningModule,
        MiniQuizService $miniQuizService,
    ): RedirectResponse {
        $attempt = $miniQuizService->startForModule($request->user(), $learningModule);

        return to_route('learn.mini-quizzes.show', $attempt);
    }

    public function show(Attempt $attempt, MiniQuizService $miniQuizService): Response
    {
        Gate::authorize('view', $attempt);

        return Inertia::render('Learn/MiniQuizzes/Show', [
            'attempt' => $miniQuizService->serializeAttempt($attempt),
        ]);
    }

    public function submit(
        SubmitMiniQuizRequest $request,
        Attempt $attempt,
        MiniQuizService $miniQuizService,
    ): RedirectResponse {
        $attempt = $miniQuizService->submit($attempt, $request->payload());

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Mini quiz berhasil dikirim.',
        ]);

        return to_route('learn.mini-quizzes.result', $attempt);
    }
}
