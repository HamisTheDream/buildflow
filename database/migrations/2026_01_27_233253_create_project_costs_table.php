<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('project_costs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();

            $table->date('cost_date')->index();

            $table->string('category')->default('material');
            // material|labor|equipment|transport|misc|service|permit|fuel|security

            $table->string('vendor')->nullable();
            $table->string('payment_method')->default('cash'); // cash|transfer|card|cheque|other

            $table->decimal('amount', 14, 2);

            $table->string('reference')->nullable(); // invoice no / receipt no / transfer ref
            $table->text('description')->nullable();

            $table->timestamps();

            $table->index(['project_id', 'category']);
            $table->index(['project_id', 'cost_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_costs');
    }
};
