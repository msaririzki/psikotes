<?php

use App\Enums\UserRoleEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone')->nullable()->after('email');
            $table->string('role')->default(UserRoleEnum::USER->value)->after('password');
            $table->string('avatar')->nullable()->after('role');
            $table->boolean('onboarding_completed')->default(false)->after('avatar');
            $table->boolean('is_active')->default(true)->after('onboarding_completed');
            $table->timestamp('last_login_at')->nullable()->after('is_active');

            $table->index('role');
            $table->index(['role', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['role']);
            $table->dropIndex(['role', 'is_active']);
            $table->dropColumn([
                'phone',
                'role',
                'avatar',
                'onboarding_completed',
                'is_active',
                'last_login_at',
            ]);
        });
    }
};
