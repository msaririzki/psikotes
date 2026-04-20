<?php

namespace App\Http\Controllers\Practice;

use App\Http\Controllers\Controller;
use App\Services\PracticeCatalogService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PracticeController extends Controller
{
    public function __invoke(Request $request, PracticeCatalogService $practiceCatalogService): Response
    {
        return Inertia::render('Practice/Index', $practiceCatalogService->overview($request->user()));
    }
}
