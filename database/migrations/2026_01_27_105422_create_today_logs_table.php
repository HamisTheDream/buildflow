<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('today_logs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->date('log_date');

            $table->text('work_done')->nullable();
            $table->text('blockers')->nullable();
            $table->text('next_steps')->nullable();

            $table->unsignedTinyInteger('progress_percent')->nullable(); // 0..100
            $table->string('weather')->nullable();

            $table->timestamps();

            $table->unique(['project_id', 'user_id', 'log_date']);
            $table->index(['project_id', 'log_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('today_logs');
    }
};
