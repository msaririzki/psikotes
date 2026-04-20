<?php

namespace App\Http\Controllers;

use App\Services\StudyPlanRecalculationService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class StudyPlanController extends Controller
{
    public function __invoke(Request $request, StudyPlanRecalculationService $studyPlanRecalculationService): Response
    {
        $this->authorize('viewStudyPlan', $request->user());

        return Inertia::render('StudyPlan/Index', [
            'studyPlan' => $studyPlanRecalculationService->forUser($request->user()),
        ]);
    }
}
