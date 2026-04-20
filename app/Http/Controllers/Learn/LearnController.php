<?php

namespace App\Http\Controllers\Learn;

use App\Http\Controllers\Controller;
use App\Services\LearningCatalogService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LearnController extends Controller
{
    public function __invoke(Request $request, LearningCatalogService $catalogService): Response
    {
        return Inertia::render('Learn/Index', $catalogService->overview($request->user()));
    }
}
