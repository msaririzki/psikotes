<?php

namespace App\Providers;

use App\Models\Attempt;
use App\Models\Category;
use App\Models\LearningModule;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\SimulationPackage;
use App\Models\StudyPlanTask;
use App\Models\Subtest;
use App\Models\User;
use App\Policies\AttemptPolicy;
use App\Policies\CategoryPolicy;
use App\Policies\LearningModulePolicy;
use App\Policies\QuestionOptionPolicy;
use App\Policies\QuestionPolicy;
use App\Policies\SimulationPackagePolicy;
use App\Policies\StudyPlanTaskPolicy;
use App\Policies\SubtestPolicy;
use App\Policies\UserPolicy;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();
        $this->registerPolicies();
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }

    protected function registerPolicies(): void
    {
        Gate::policy(Category::class, CategoryPolicy::class);
        Gate::policy(Subtest::class, SubtestPolicy::class);
        Gate::policy(LearningModule::class, LearningModulePolicy::class);
        Gate::policy(Attempt::class, AttemptPolicy::class);
        Gate::policy(Question::class, QuestionPolicy::class);
        Gate::policy(QuestionOption::class, QuestionOptionPolicy::class);
        Gate::policy(SimulationPackage::class, SimulationPackagePolicy::class);
        Gate::policy(StudyPlanTask::class, StudyPlanTaskPolicy::class);
        Gate::policy(User::class, UserPolicy::class);
    }
}
