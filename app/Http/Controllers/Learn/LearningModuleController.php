<?php

namespace App\Http\Controllers\Learn;

use App\Http\Controllers\Controller;
use App\Models\LearningModule;
use App\Services\LearningCatalogService;
use App\Services\LearningProgressService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LearningModuleController extends Controller
{
    public function show(
        LearningModule $learningModule,
        Request $request,
        LearningCatalogService $catalogService,
        LearningProgressService $progressService,
    ): Response {
        $this->authorize('viewLearn', $learningModule);

        $progressService->touchForViewing($request->user(), $learningModule);

        return Inertia::render('Learn/Modules/Show', $catalogService->moduleDetail($learningModule, $request->user()));
    }
}
