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
        Schema::create('owner_expenses', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->integer('amount_cents');
            $table->string('currency')->default('NGN');
            $table->date('date');
            $table->string('category')->nullable(); // e.g., 'marketing', 'server', 'salary'
            $table->text('details')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('owner_expenses');
    }
};
