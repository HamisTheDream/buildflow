<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('paystack_webhook_events', function (Blueprint $table) {
            $table->id();

            $table->string('event')->index(); // e.g. charge.success
            $table->string('reference')->nullable()->index();
            $table->string('signature')->nullable();
            $table->json('payload');

            $table->string('ip')->nullable();
            $table->string('user_agent', 512)->nullable();

            $table->timestamp('received_at')->useCurrent();
            $table->timestamps();

            $table->index(['event', 'received_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('paystack_webhook_events');
    }
};
