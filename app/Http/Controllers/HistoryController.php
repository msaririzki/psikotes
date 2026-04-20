<?php

namespace App\Http\Controllers;

use App\Services\HistoryTimelineService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class HistoryController extends Controller
{
    public function __invoke(Request $request, HistoryTimelineService $historyTimelineService): Response
    {
        $this->authorize('viewHistoryCenter', $request->user());

        return Inertia::render('History/Index', $historyTimelineService->build(
            $request->user(),
            (string) $request->string('type', 'all'),
            max((int) $request->integer('per_page', 12), 1),
            max((int) $request->integer('page', 1), 1),
        ));
    }
}
