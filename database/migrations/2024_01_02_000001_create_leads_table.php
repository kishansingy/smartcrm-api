<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('company')->nullable();
            $table->string('job_title')->nullable();
            $table->enum('source', [
                'website', 'whatsapp', 'email', 'phone',
                'referral', 'social_media', 'campaign', 'manual', 'import', 'other',
            ])->default('manual');
            $table->enum('status', [
                'new', 'contacted', 'qualified', 'proposal',
                'negotiation', 'won', 'lost', 'unqualified',
            ])->default('new');
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            $table->unsignedSmallInteger('score')->default(0);
            $table->text('notes')->nullable();
            $table->json('meta')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'assigned_to']);
            $table->index(['tenant_id', 'created_at']);
        });

        Schema::create('lead_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type');  // created, status_changed, assigned, note, call, email, whatsapp
            $table->text('description');
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['lead_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lead_activities');
        Schema::dropIfExists('leads');
    }
};
