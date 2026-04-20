<?php

namespace App\Http\Controllers;

use App\Services\ProgressAggregationService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProgressController extends Controller
{
    public function __invoke(Request $request, ProgressAggregationService $progressAggregationService): Response
    {
        $this->authorize('viewProgressCenter', $request->user());

        return Inertia::render('Progress/Index', $progressAggregationService->dashboard($request->user()));
    }
}
