<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('project_reports', function (Blueprint $table) {
            $table->id();

            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('generated_by')->constrained('users')->cascadeOnDelete();

            $table->date('from_date');
            $table->date('to_date');

            $table->string('title')->nullable();
            $table->json('options')->nullable(); // what included

            $table->string('pdf_disk')->default('public');
            $table->string('pdf_path');

            $table->string('share_token')->unique();
            $table->timestamp('share_expires_at')->nullable();

            $table->timestamps();

            $table->index(['project_id', 'from_date', 'to_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_reports');
    }
};
