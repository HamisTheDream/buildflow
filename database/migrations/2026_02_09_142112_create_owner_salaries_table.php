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
        Schema::create('owner_salaries', function (Blueprint $table) {
            $table->id();
            $table->string('employee_name');
            $table->string('role')->nullable();
            $table->integer('amount_cents');
            $table->string('currency')->default('NGN');
            $table->string('frequency')->default('monthly'); // monthly, weekly
            $table->date('next_payment_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('owner_salaries');
    }
};
