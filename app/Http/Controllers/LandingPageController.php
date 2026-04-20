<?php

namespace App\Http\Controllers;

use App\Services\DashboardOverviewService;
use Inertia\Inertia;
use Inertia\Response;
use Laravel\Fortify\Features;

class LandingPageController extends Controller
{
    public function __invoke(DashboardOverviewService $overviewService): Response
    {
        return Inertia::render('Welcome', [
            'canRegister' => Features::enabled(Features::registration()),
            'stats' => $overviewService->publicStats(),
        ]);
    }
}
