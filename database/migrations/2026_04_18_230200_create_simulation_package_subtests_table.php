<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('simulation_package_subtests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('simulation_package_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subtest_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('question_count');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['simulation_package_id', 'subtest_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('simulation_package_subtests');
    }
};
