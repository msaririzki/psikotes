<?php

use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\LearningModuleController;
use App\Http\Controllers\Admin\QuestionController;
use App\Http\Controllers\Admin\QuestionOptionController;
use App\Http\Controllers\Admin\SimulationPackageController as AdminSimulationPackageController;
use App\Http\Controllers\Admin\SubtestController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HistoryController;
use App\Http\Controllers\LandingPageController;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\Learn\CompleteLearningModuleController;
use App\Http\Controllers\Learn\LearnCategoryController;
use App\Http\Controllers\Learn\LearnController;
use App\Http\Controllers\Learn\LearningModuleController as LearnLearningModuleController;
use App\Http\Controllers\Learn\LearnSubtestController;
use App\Http\Controllers\Learn\MiniQuizController;
use App\Http\Controllers\Learn\MiniQuizResultController;
use App\Http\Controllers\Practice\PracticeAnswerController;
use App\Http\Controllers\Practice\PracticeAttemptController;
use App\Http\Controllers\Practice\PracticeController;
use App\Http\Controllers\Practice\PracticeResultController;
use App\Http\Controllers\Practice\PracticeSubtestController;
use App\Http\Controllers\ProgressController;
use App\Http\Controllers\StudyPlanController;
use App\Http\Controllers\StudyPlanTaskController;
use App\Http\Controllers\Simulation\SimulationAttemptController;
use App\Http\Controllers\Simulation\SimulationController;
use App\Http\Controllers\Simulation\SimulationPackageController;
use App\Http\Controllers\Simulation\SimulationProgressController;
use App\Http\Controllers\Simulation\SimulationResultController;
use Illuminate\Support\Facades\Route;

Route::get('/', LandingPageController::class)->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', DashboardController::class)->name('dashboard');
    Route::get('onboarding', [OnboardingController::class, 'show'])->name('onboarding.show');
    Route::post('onboarding', [OnboardingController::class, 'store'])->name('onboarding.store');
    Route::get('history', HistoryController::class)->name('history');
    Route::get('progress', ProgressController::class)->name('progress');
    Route::get('study-plan', StudyPlanController::class)->name('study-plan');
    Route::patch('study-plan/tasks/{studyPlanTask}', [StudyPlanTaskController::class, 'update'])->name('study-plan.tasks.update');

    Route::prefix('learn')
        ->name('learn.')
        ->scopeBindings()
        ->group(function () {
            Route::get('/', LearnController::class)->name('index');
            Route::get('categories/{category:slug}', [LearnCategoryController::class, 'show'])->name('categories.show');
            Route::get('categories/{category:slug}/subtests/{subtest:slug}', [LearnSubtestController::class, 'show'])->name('subtests.show');
            Route::get('modules/{learningModule:slug}', [LearnLearningModuleController::class, 'show'])->name('modules.show');
            Route::post('modules/{learningModule:slug}/complete', CompleteLearningModuleController::class)->name('modules.complete');
            Route::post('modules/{learningModule:slug}/mini-quiz', [MiniQuizController::class, 'store'])->name('mini-quizzes.store');
            Route::get('mini-quizzes/{attempt}', [MiniQuizController::class, 'show'])->name('mini-quizzes.show');
            Route::post('mini-quizzes/{attempt}/submit', [MiniQuizController::class, 'submit'])->name('mini-quizzes.submit');
            Route::get('mini-quizzes/{attempt}/result', MiniQuizResultController::class)->name('mini-quizzes.result');
        });

    Route::prefix('practice')
        ->name('practice.')
        ->scopeBindings()
        ->group(function () {
            Route::get('/', PracticeController::class)->name('index');
            Route::get('subtests/{subtest:slug}', [PracticeSubtestController::class, 'show'])->name('subtests.show');
            Route::post('subtests/{subtest:slug}/attempts', [PracticeAttemptController::class, 'store'])->name('attempts.store');
            Route::get('attempts/{attempt}', [PracticeAttemptController::class, 'show'])->name('attempts.show');
            Route::post('attempts/{attempt}/answers', PracticeAnswerController::class)->name('attempts.answers.store');
            Route::post('attempts/{attempt}/submit', [PracticeAttemptController::class, 'submit'])->name('attempts.submit');
            Route::get('attempts/{attempt}/result', PracticeResultController::class)->name('attempts.result');
        });

    Route::prefix('simulations')
        ->name('simulations.')
        ->scopeBindings()
        ->group(function () {
            Route::get('/', SimulationController::class)->name('index');
            Route::get('attempts/{attempt}', [SimulationAttemptController::class, 'show'])->name('attempts.show');
            Route::post('attempts/{attempt}/progress', SimulationProgressController::class)->name('attempts.progress');
            Route::post('attempts/{attempt}/submit', [SimulationAttemptController::class, 'submit'])->name('attempts.submit');
            Route::get('attempts/{attempt}/result', SimulationResultController::class)->name('attempts.result');
            Route::post('{simulationPackage:slug}/attempts', [SimulationAttemptController::class, 'store'])->name('attempts.store');
            Route::get('{simulationPackage:slug}', [SimulationPackageController::class, 'show'])->name('show');
        });
});

Route::middleware(['auth', 'verified', 'admin'])
    ->prefix('admin')
    ->name('admin.')
    ->scopeBindings()
    ->group(function () {
        Route::get('/', AdminDashboardController::class)->name('dashboard');

        Route::patch('categories/{category}/activity', [CategoryController::class, 'toggleActivity'])->name('categories.activity');
        Route::resource('categories', CategoryController::class)->except('show');

        Route::patch('subtests/{subtest}/activity', [SubtestController::class, 'toggleActivity'])->name('subtests.activity');
        Route::resource('subtests', SubtestController::class)->except('show');

        Route::patch('learning-modules/{learningModule}/publication', [LearningModuleController::class, 'togglePublication'])->name('learning-modules.publication');
        Route::resource('learning-modules', LearningModuleController::class)->except('show');

        Route::patch('questions/{question}/status', [QuestionController::class, 'updateStatus'])->name('questions.status');
        Route::resource('questions', QuestionController::class)->except('show');

        Route::get('questions/{question}/options', [QuestionOptionController::class, 'index'])->name('questions.options.index');
        Route::get('questions/{question}/options/create', [QuestionOptionController::class, 'create'])->name('questions.options.create');
        Route::post('questions/{question}/options', [QuestionOptionController::class, 'store'])->name('questions.options.store');
        Route::get('questions/{question}/options/{option}/edit', [QuestionOptionController::class, 'edit'])->name('questions.options.edit');
        Route::put('questions/{question}/options/{option}', [QuestionOptionController::class, 'update'])->name('questions.options.update');
        Route::delete('questions/{question}/options/{option}', [QuestionOptionController::class, 'destroy'])->name('questions.options.destroy');

        Route::patch('simulation-packages/{simulationPackage}/publication', [AdminSimulationPackageController::class, 'togglePublication'])->name('simulation-packages.publication');
        Route::resource('simulation-packages', AdminSimulationPackageController::class)->except('show');
    });

require __DIR__.'/settings.php';
