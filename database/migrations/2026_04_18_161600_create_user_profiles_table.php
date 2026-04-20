<?php

use App\Enums\LearningLevelEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('education_level')->nullable();
            $table->string('target_exam')->nullable();
            $table->string('learning_level')->default(LearningLevelEnum::BEGINNER->value);
            $table->unsignedSmallInteger('target_daily_minutes')->nullable();
            $table->string('preferred_focus')->nullable();
            $table->json('onboarding_answers')->nullable();
            $table->timestamps();

            $table->unique('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_profiles');
    }
};
