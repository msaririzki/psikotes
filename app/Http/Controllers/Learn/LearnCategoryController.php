<?php

namespace App\Http\Controllers\Learn;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Services\LearningCatalogService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LearnCategoryController extends Controller
{
    public function show(Category $category, Request $request, LearningCatalogService $catalogService): Response
    {
        abort_unless($request->user()->isAdmin() || $category->is_active, 404);

        return Inertia::render('Learn/Categories/Show', $catalogService->categoryDetail($category, $request->user()));
    }
}
