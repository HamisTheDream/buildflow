<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('project_tasks', function (Blueprint $table) {
            $table->id();

            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();

            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();

            $table->string('title');
            $table->text('description')->nullable();

            $table->string('status')->default('todo'); // todo|doing|done|blocked
            $table->string('priority')->default('normal'); // low|normal|high|urgent

            $table->date('due_date')->nullable();

            $table->timestamps();

            $table->index(['project_id', 'status']);
            $table->index(['project_id', 'assigned_to']);
            $table->index(['project_id', 'due_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_tasks');
    }
};
