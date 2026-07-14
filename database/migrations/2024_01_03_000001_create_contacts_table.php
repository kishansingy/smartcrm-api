<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('mobile', 30)->nullable();
            $table->string('company')->nullable();
            $table->string('job_title')->nullable();
            $table->string('department')->nullable();
            $table->string('website')->nullable();
            $table->json('address')->nullable();
            $table->enum('type', ['individual', 'business', 'partner', 'vendor', 'other'])->default('individual');
            $table->enum('status', ['active', 'inactive', 'blocked'])->default('active');
            $table->string('source', 50)->nullable();
            $table->json('tags')->nullable();
            $table->text('notes')->nullable();
            $table->json('meta')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'type']);
            $table->index(['tenant_id', 'created_at']);
        });

        Schema::create('contact_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contact_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->text('content');
            $table->string('type', 30)->default('note'); // note, call, email, whatsapp, meeting
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['contact_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_notes');
        Schema::dropIfExists('contacts');
    }
};
