<?php

namespace App\Http\Controllers\Practice;

use App\Enums\AttemptStatusEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Practice\StartPracticeRequest;
use App\Http\Requests\Practice\SubmitPracticeRequest;
use App\Models\Attempt;
use App\Models\Subtest;
use App\Services\PracticeSessionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class PracticeAttemptController extends Controller
{
    public function store(
        StartPracticeRequest $request,
        Subtest $subtest,
        PracticeSessionService $practiceSessionService,
    ): RedirectResponse {
        $payload = $request->payload();

        $attempt = $practiceSessionService->start(
            $request->user(),
            $subtest,
            $payload['difficulty'],
            $payload['question_count'],
            $payload['timer_minutes'],
        );

        return to_route('practice.attempts.show', $attempt);
    }

    public function show(Attempt $attempt, PracticeSessionService $practiceSessionService): Response|RedirectResponse
    {
        Gate::authorize('viewPractice', $attempt);

        if ($attempt->status === AttemptStatusEnum::SUBMITTED) {
            return to_route('practice.attempts.result', $attempt);
        }

        return Inertia::render('Practice/Attempts/Show', [
            'attempt' => $practiceSessionService->serializeSession($attempt),
        ]);
    }

    public function submit(
        SubmitPracticeRequest $request,
        Attempt $attempt,
        PracticeSessionService $practiceSessionService,
    ): RedirectResponse {
        $attempt = $practiceSessionService->submit($attempt, $request->payload());

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Latihan berhasil dikirim.',
        ]);

        return to_route('practice.attempts.result', $attempt);
    }
}
