<?php

namespace App\Http\Controllers;

use App\Services\DashboardOverviewService;
use App\Services\ProgressAggregationService;
use App\Services\StudyPlanRecalculationService;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(
        DashboardOverviewService $overviewService,
        ProgressAggregationService $progressAggregationService,
        StudyPlanRecalculationService $studyPlanRecalculationService,
    ): Response
    {
        $user = auth()->user();

        return Inertia::render('Dashboard', [
            'overview' => $overviewService->forUser($user),
            'progressSnapshot' => $progressAggregationService->dashboard($user),
            'studyPlan' => $studyPlanRecalculationService->forUser($user),
        ]);
    }
}
