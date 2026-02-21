<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'current_organization_id')) {
                $table->foreignId('current_organization_id')->nullable()
                    ->after('id')
                    ->constrained('organizations')
                    ->nullOnDelete();
            }

            if (!Schema::hasColumn('users', 'is_invited_only')) {
                $table->boolean('is_invited_only')->default(false)->after('password');
            }

            if (!Schema::hasColumn('users', 'phone')) {
                $table->string('phone')->nullable()->after('email');
            }
            if (!Schema::hasColumn('users', 'avatar_path')) {
                $table->string('avatar_path')->nullable()->after('phone');
            }
            if (!Schema::hasColumn('users', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('avatar_path');
            }

            // Safely add indexes
            $indexes = Schema::getIndexes('users');
            $indexNames = array_column($indexes, 'name');

            if (!in_array('users_current_organization_id_index', $indexNames)) {
                $table->index(['current_organization_id']);
            }
            if (!in_array('users_is_invited_only_index', $indexNames)) {
                $table->index(['is_invited_only']);
            }
            if (!in_array('users_is_active_index', $indexNames)) {
                $table->index(['is_active']);
            }
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
