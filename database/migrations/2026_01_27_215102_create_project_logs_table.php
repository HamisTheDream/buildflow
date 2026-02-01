<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('project_logs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->date('log_date')->index(); // date the log is about
            $table->time('log_time')->nullable(); // optional time

            $table->string('type')->default('general'); 
            // general|site_visit|progress|material|labor|safety|client|finance|issue

            $table->string('title')->nullable();
            $table->text('body')->nullable();

            $table->timestamps();

            $table->index(['project_id', 'log_date']);
            $table->index(['project_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_logs');
    }
};
