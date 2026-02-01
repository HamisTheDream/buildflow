<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('payment_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_id')->constrained('payments')->cascadeOnDelete();
            $table->foreignId('admin_id')->constrained('admins')->cascadeOnDelete();

            $table->string('note', 500);
            $table->timestamps();

            $table->index(['payment_id','created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_notes');
    }
};
