<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attempts', function (Blueprint $table) {
            $table
                ->foreignId('learning_module_id')
                ->nullable()
                ->after('subtest_id')
                ->constrained('learning_modules')
                ->nullOnDelete();

            $table->index(['learning_module_id', 'mode', 'status']);
        });
    }

    public function down(): void
    {
        Schema::table('attempts', function (Blueprint $table) {
            $table->dropConstrainedForeignId('learning_module_id');
        });
    }
};
