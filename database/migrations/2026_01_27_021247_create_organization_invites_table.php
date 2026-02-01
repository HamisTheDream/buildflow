<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('organization_invites', function (Blueprint $table) {
            $table->id();

            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();

            $table->string('email');
            $table->string('role')->default('member'); // admin|member (owner never invited)

            $table->string('token')->unique();
            $table->timestamp('expires_at')->nullable();

            $table->foreignId('invited_by')->constrained('users')->cascadeOnDelete();

            $table->timestamp('accepted_at')->nullable();
            $table->foreignId('accepted_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();

            $table->index(['organization_id', 'email']);
            $table->index(['email']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('organization_invites');
    }
};
