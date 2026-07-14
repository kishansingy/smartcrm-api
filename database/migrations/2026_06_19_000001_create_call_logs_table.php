<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('call_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();       // agent who made the call
            $table->foreignId('contact_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('lead_id')->nullable()->constrained()->nullOnDelete();
            $table->string('phone_number', 30);
            $table->enum('direction', ['outbound', 'inbound'])->default('outbound');
            $table->enum('status', ['initiated', 'ringing', 'in_progress', 'completed', 'failed', 'no_answer', 'busy'])->default('initiated');
            $table->integer('duration')->nullable();                                        // seconds
            $table->string('provider_call_id')->nullable();                                // Twilio/VAPI SID
            $table->string('recording_url')->nullable();
            $table->text('transcript')->nullable();
            $table->text('ai_summary')->nullable();
            $table->json('ai_insights')->nullable();                                        // sentiment, action items, keywords
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'created_at']);
            $table->index(['contact_id']);
            $table->index(['lead_id']);
            $table->index(['user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('call_logs');
    }
};
