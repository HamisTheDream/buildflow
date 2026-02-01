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
        Schema::create('plans', function (Blueprint $table) {
            $table->id();

            $table->string('key')->unique(); // free|starter|pro
            $table->string('name');
            $table->unsignedInteger('price_monthly_cents')->default(0);

            $table->unsignedInteger('max_projects')->default(1);
            $table->unsignedInteger('max_members')->default(3);
            $table->unsignedBigInteger('max_storage_mb')->default(200);

            $table->boolean('can_share_reports')->default(true);
            $table->boolean('can_password_protect_reports')->default(false);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};
