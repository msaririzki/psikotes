<?php

namespace App\Http\Controllers\Learn;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Subtest;
use App\Services\LearningCatalogService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LearnSubtestController extends Controller
{
    public function show(
        Category $category,
        Subtest $subtest,
        Request $request,
        LearningCatalogService $catalogService,
    ): Response {
        abort_unless(
            $request->user()->isAdmin() || ($category->is_active && $subtest->is_active),
            404,
        );

        return Inertia::render('Learn/Subtests/Show', $catalogService->subtestDetail($category, $subtest, $request->user()));
    }
}
