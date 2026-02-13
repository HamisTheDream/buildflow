<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('support_ticket_notes', function (Blueprint $table) {
            // Make admin_id nullable (users can also create notes now)
            $table->foreignId('admin_id')->nullable()->change();

            // Add user_id for user replies
            $table->foreignId('user_id')->nullable()->after('admin_id')->constrained('users')->nullOnDelete();

            // Add visibility flag (false = internal admin-only note)
            $table->boolean('is_public')->default(true)->after('note');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('support_ticket_notes', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_id');
            $table->dropColumn('is_public');
        });
    }
};
