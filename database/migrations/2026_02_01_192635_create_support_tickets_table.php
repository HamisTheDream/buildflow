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
        Schema::create('support_tickets', function (Blueprint $table) {
            $table->id();

            $table->foreignId('organization_id')->constrained('organizations')->cascadeOnDelete();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();

            $table->string('subject');
            $table->text('message');

            $table->string('status')->default('open'); // open, pending, resolved, closed
            $table->string('priority')->default('normal'); // low, normal, high, urgent
            $table->string('category')->nullable(); // billing, bug, feature, other

            $table->timestamps();

            $table->index(['organization_id','status']);
            $table->index(['status','priority']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('support_tickets');
    }
};
