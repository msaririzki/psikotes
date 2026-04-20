<?php

namespace App\Http\Controllers\Practice;

use App\Http\Controllers\Controller;
use App\Models\Subtest;
use App\Services\PracticeCatalogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class PracticeSubtestController extends Controller
{
    public function show(
        Request $request,
        Subtest $subtest,
        PracticeCatalogService $practiceCatalogService,
    ): Response {
        Gate::authorize('viewPractice', $subtest);

        return Inertia::render('Practice/Subtests/Show', $practiceCatalogService->subtestDetail($subtest, $request->user()));
    }
}
