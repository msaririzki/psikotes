<?php

namespace App\Http\Controllers\Practice;

use App\Http\Controllers\Controller;
use App\Http\Requests\Practice\SavePracticeAnswersRequest;
use App\Models\Attempt;
use App\Services\PracticeSessionService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

class PracticeAnswerController extends Controller
{
    public function __invoke(
        SavePracticeAnswersRequest $request,
        Attempt $attempt,
        PracticeSessionService $practiceSessionService,
    ): RedirectResponse {
        $practiceSessionService->saveAnswers($attempt, $request->payload());

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Jawaban latihan berhasil disimpan.',
        ]);

        return back();
    }
}
