<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('payments')) return;

        Schema::create('payments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('plan_id')->constrained('plans')->cascadeOnDelete();

            $table->string('gateway')->default('paystack');
            $table->string('reference')->unique();

            $table->unsignedInteger('amount_cents');
            $table->string('currency')->default('NGN');

            $table->string('status')->default('initialized'); // initialized|success|failed
            $table->json('meta')->nullable(); // store raw verify response safely

            $table->timestamps();

            $table->index(['organization_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
