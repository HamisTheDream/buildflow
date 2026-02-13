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
        Schema::create('owner_deals', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->integer('value_cents')->default(0);
            $table->string('currency')->default('NGN');
            $table->string('status')->default('open'); // open, won, lost
            $table->foreignId('organization_id')->nullable()->constrained()->nullOnDelete();
            $table->text('details')->nullable();
            $table->date('close_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('owner_deals');
    }
};
