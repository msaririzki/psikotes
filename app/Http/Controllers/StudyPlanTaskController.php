<?php

namespace App\Http\Controllers;

use App\Http\Requests\StudyPlan\UpdateStudyPlanTaskRequest;
use App\Models\StudyPlanTask;
use App\Services\StudyPlanTaskTransitionService;
use Illuminate\Http\RedirectResponse;

class StudyPlanTaskController extends Controller
{
    public function update(
        UpdateStudyPlanTaskRequest $request,
        StudyPlanTask $studyPlanTask,
        StudyPlanTaskTransitionService $studyPlanTaskTransitionService,
    ): RedirectResponse {
        $payload = $request->payload();

        $studyPlanTaskTransitionService->apply($studyPlanTask, $payload);

        return redirect($payload['redirect_to']);
    }
}
