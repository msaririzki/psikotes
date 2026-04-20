<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\DashboardOverviewService;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(DashboardOverviewService $overviewService): Response
    {
        Gate::authorize('accessAdminArea', User::class);

        return Inertia::render('Admin/Dashboard', [
            'overview' => $overviewService->forAdmin(),
        ]);
    }
}
