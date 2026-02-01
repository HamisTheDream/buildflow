<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('current_organization_id')->nullable()
                ->after('id')
                ->constrained('organizations')
                ->nullOnDelete();

            $table->boolean('is_invited_only')->default(false)->after('password');

            $table->string('phone')->nullable()->after('email');
            $table->string('avatar_path')->nullable()->after('phone');

            $table->index(['current_organization_id']);
            $table->index(['is_invited_only']);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('current_organization_id');
            $table->dropColumn(['is_invited_only', 'phone', 'avatar_path']);
        });
    }
};
