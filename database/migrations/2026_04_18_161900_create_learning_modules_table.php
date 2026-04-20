<?php

use App\Enums\ModuleLevelEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('learning_modules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subtest_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('summary')->nullable();
            $table->longText('content');
            $table->longText('tips')->nullable();
            $table->longText('tricks')->nullable();
            $table->string('level')->default(ModuleLevelEnum::BASIC->value);
            $table->unsignedSmallInteger('estimated_minutes')->nullable();
            $table->boolean('is_published')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['subtest_id', 'level']);
            $table->index(['is_published', 'published_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('learning_modules');
    }
};
