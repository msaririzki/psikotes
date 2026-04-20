<?php

namespace Database\Seeders;

use App\Enums\AttemptModeEnum;
use App\Enums\LearningLevelEnum;
use App\Enums\PracticeDifficultyFilterEnum;
use App\Services\LearningProgressService;
use App\Services\MiniQuizService;
use App\Services\PracticeSessionService;
use App\Services\SimulationSessionService;
use App\Services\StudyPlanRecalculationService;
use App\Models\LearningModule;
use App\Models\SimulationPackage;
use App\Models\User;
use Illuminate\Database\Seeder;

class DemoJourneySeeder extends Seeder
{
    public function run(): void
    {
        $demoUser = User::query()->where('email', 'peserta@prikotes.test')->first();
        $freshUser = User::query()->where('email', 'userbaru@prikotes.test')->first();

        if (! $demoUser) {
            return;
        }

        $demoUser->forceFill(['onboarding_completed' => true])->save();

        $demoUser->profile()->updateOrCreate(
            ['user_id' => $demoUser->id],
            [
                'education_level' => 'SMA / SMK',
                'target_exam' => 'Psikotes Polri',
                'learning_level' => LearningLevelEnum::INTERMEDIATE,
                'target_daily_minutes' => 60,
                'preferred_focus' => 'balanced',
                'onboarding_answers' => [
                    'source' => 'demo_seed',
                    'completed_at' => now()->subDays(12)->toIso8601String(),
                ],
            ],
        );

        if ($freshUser) {
            $freshUser->forceFill(['onboarding_completed' => false])->save();

            $freshUser->profile()->updateOrCreate(
                ['user_id' => $freshUser->id],
                [
                    'target_exam' => 'Psikotes Polri',
                    'learning_level' => LearningLevelEnum::BEGINNER,
                    'onboarding_answers' => [
                        'source' => 'registration_default',
                    ],
                ],
            );
        }

        if ($demoUser->attempts()->exists()) {
            app(StudyPlanRecalculationService::class)->forUser($demoUser);

            return;
        }

        /** @var LearningProgressService $learningProgressService */
        $learningProgressService = app(LearningProgressService::class);
        /** @var MiniQuizService $miniQuizService */
        $miniQuizService = app(MiniQuizService::class);
        /** @var PracticeSessionService $practiceSessionService */
        $practiceSessionService = app(PracticeSessionService::class);
        /** @var SimulationSessionService $simulationSessionService */
        $simulationSessionService = app(SimulationSessionService::class);
        /** @var StudyPlanRecalculationService $studyPlanRecalculationService */
        $studyPlanRecalculationService = app(StudyPlanRecalculationService::class);

        $verbalModule = LearningModule::query()->where('slug', 'modul-verbal-fondasi')->first();
        $numerikModule = LearningModule::query()->where('slug', 'modul-numerik-dasar')->first();
        $figuralModule = LearningModule::query()->where('slug', 'modul-figural-pola')->first();

        if ($verbalModule) {
            $learningProgressService->markCompleted($demoUser, $verbalModule);
        }

        if ($numerikModule) {
            $learningProgressService->markCompleted($demoUser, $numerikModule);
        }

        if ($figuralModule) {
            $learningProgressService->touchForViewing($demoUser, $figuralModule);
        }

        if ($verbalModule) {
            $miniQuizAttempt = $miniQuizService->startForModule($demoUser, $verbalModule);
            $miniQuizAttempt->load('attemptQuestions.question.options');

            $miniQuizAnswers = $miniQuizAttempt->attemptQuestions
                ->mapWithKeys(function ($attemptQuestion, int $index): array {
                    $question = $attemptQuestion->question;
                    $selectedOption = $index < 4
                        ? $question->options->firstWhere('is_correct', true)
                        : $question->options->firstWhere('is_correct', false);

                    return [$question->id => $selectedOption?->id];
                })
                ->filter()
                ->all();

            $miniQuizService->submit($miniQuizAttempt, $miniQuizAnswers);
        }

        if ($verbalModule?->subtest) {
            $practiceAttempt = $practiceSessionService->start(
                $demoUser,
                $verbalModule->subtest,
                PracticeDifficultyFilterEnum::ALL,
                5,
                15,
            );

            $practiceAttempt->load('attemptQuestions.question.options');

            $practiceAnswers = $practiceAttempt->attemptQuestions
                ->mapWithKeys(function ($attemptQuestion, int $index): array {
                    $question = $attemptQuestion->question;
                    $selectedOption = $index < 4
                        ? $question->options->firstWhere('is_correct', true)
                        : $question->options->firstWhere('is_correct', false);

                    return [$question->id => $selectedOption?->id];
                })
                ->filter()
                ->all();

            $practiceSessionService->submit($practiceAttempt, $practiceAnswers);
        }

        if ($numerikModule?->subtest) {
            $practiceAttempt = $practiceSessionService->start(
                $demoUser,
                $numerikModule->subtest,
                PracticeDifficultyFilterEnum::ALL,
                5,
                null,
            );

            $practiceAttempt->load('attemptQuestions.question.options');

            $practiceAnswers = $practiceAttempt->attemptQuestions
                ->mapWithKeys(function ($attemptQuestion, int $index): array {
                    $question = $attemptQuestion->question;
                    $selectedOption = $index < 3
                        ? $question->options->firstWhere('is_correct', true)
                        : $question->options->firstWhere('is_correct', false);

                    return [$question->id => $selectedOption?->id];
                })
                ->filter()
                ->all();

            $practiceSessionService->submit($practiceAttempt, $practiceAnswers);
        }

        $simulationPackage = SimulationPackage::query()
            ->where('slug', 'simulasi-dasar-akademik')
            ->first();

        if ($simulationPackage) {
            $simulationAttempt = $simulationSessionService->start($demoUser, $simulationPackage);
            $simulationAttempt->load('attemptQuestions');

            $simulationAnswers = $simulationAttempt->attemptQuestions
                ->take(18)
                ->mapWithKeys(function ($attemptQuestion): array {
                    $options = collect(data_get($attemptQuestion->snapshot, 'options', []));
                    $correctOption = $options->firstWhere('is_correct', true);

                    return [$attemptQuestion->question_id => $correctOption['id'] ?? null];
                })
                ->filter()
                ->all();

            $flags = $simulationAttempt->attemptQuestions
                ->take(3)
                ->mapWithKeys(fn ($attemptQuestion): array => [$attemptQuestion->question_id => true])
                ->all();

            $simulationSessionService->submit($simulationAttempt, $simulationAnswers, $flags);
        }

        $studyPlanRecalculationService->forUser($demoUser);
    }
}
