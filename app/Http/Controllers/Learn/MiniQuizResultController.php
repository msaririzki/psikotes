<?php

namespace App\Http\Controllers\Learn;

use App\Http\Controllers\Controller;
use App\Models\Attempt;
use App\Services\MiniQuizService;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class MiniQuizResultController extends Controller
{
    public function __invoke(Attempt $attempt, MiniQuizService $miniQuizService): Response
    {
        Gate::authorize('viewResult', $attempt);

        return Inertia::render('Learn/MiniQuizzes/Result', $miniQuizService->resultPayload($attempt));
    }
}
