<?php

namespace App\Http\Controllers\Practice;

use App\Http\Controllers\Controller;
use App\Models\Attempt;
use App\Services\PracticeSessionService;
use App\Services\StudyPlanTaskAutoResolutionService;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class PracticeResultController extends Controller
{
    public function __invoke(
        Attempt $attempt,
        PracticeSessionService $practiceSessionService,
        StudyPlanTaskAutoResolutionService $studyPlanTaskAutoResolutionService,
    ): Response
    {
        Gate::authorize('viewPracticeResult', $attempt);

        $studyPlanTaskAutoResolutionService->practiceResultViewed($attempt);

        return Inertia::render('Practice/Attempts/Result', $practiceSessionService->resultPayload($attempt));
    }
}
