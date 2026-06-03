<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workspaces', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('plan')->default('pro');
            $table->string('timezone')->default('Asia/Karachi');
            $table->json('settings')->nullable();
            $table->timestamps();
        });

        Schema::create('workspace_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workspace_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('role')->default('admin');
            $table->timestamps();
            $table->unique(['workspace_id', 'user_id']);
        });

        Schema::create('whatsapp_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workspace_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('phone_number');
            $table->string('provider')->default('meta');
            $table->string('status')->default('connected');
            $table->string('quality_rating')->default('high');
            $table->timestamp('last_synced_at')->nullable();
            $table->json('settings')->nullable();
            $table->timestamps();
        });

        Schema::create('contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workspace_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('phone_number');
            $table->string('email')->nullable();
            $table->string('avatar')->nullable();
            $table->string('status')->default('new_lead');
            $table->string('source')->default('whatsapp');
            $table->decimal('deal_value', 12, 2)->default(0);
            $table->string('owner_name')->nullable();
            $table->json('tags')->nullable();
            $table->timestamps();
            $table->unique(['workspace_id', 'phone_number']);
        });

        Schema::create('conversations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workspace_id')->constrained()->cascadeOnDelete();
            $table->foreignId('whatsapp_account_id')->constrained()->cascadeOnDelete();
            $table->foreignId('contact_id')->constrained()->cascadeOnDelete();
            $table->string('status')->default('open');
            $table->string('priority')->default('normal');
            $table->string('assigned_to')->nullable();
            $table->unsignedInteger('unread_count')->default(0);
            $table->timestamp('last_message_at')->nullable();
            $table->timestamps();
        });

        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained()->cascadeOnDelete();
            $table->string('direction');
            $table->string('sender_type')->default('contact');
            $table->text('body');
            $table->string('message_type')->default('text');
            $table->string('status')->default('sent');
            $table->boolean('ai_generated')->default(false);
            $table->json('metadata')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
        });

        Schema::create('ai_automations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workspace_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('trigger');
            $table->string('status')->default('active');
            $table->unsignedInteger('runs_count')->default(0);
            $table->decimal('success_rate', 5, 2)->default(0);
            $table->json('flow')->nullable();
            $table->timestamps();
        });

        Schema::create('ai_training_sources', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workspace_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('type')->default('document');
            $table->string('status')->default('indexed');
            $table->unsignedInteger('chunks_count')->default(0);
            $table->timestamp('trained_at')->nullable();
            $table->timestamps();
        });

        Schema::create('broadcast_campaigns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workspace_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('status')->default('draft');
            $table->unsignedInteger('audience_count')->default(0);
            $table->unsignedInteger('sent_count')->default(0);
            $table->unsignedInteger('delivered_count')->default(0);
            $table->unsignedInteger('replied_count')->default(0);
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamps();
        });

        Schema::create('analytics_daily_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workspace_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->unsignedInteger('received_messages')->default(0);
            $table->unsignedInteger('sent_messages')->default(0);
            $table->unsignedInteger('ai_replies')->default(0);
            $table->unsignedInteger('leads_captured')->default(0);
            $table->decimal('response_rate', 5, 2)->default(0);
            $table->timestamps();
            $table->unique(['workspace_id', 'date']);
        });

        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workspace_id')->constrained()->cascadeOnDelete();
            $table->string('type');
            $table->string('description');
            $table->json('properties')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
        Schema::dropIfExists('analytics_daily_snapshots');
        Schema::dropIfExists('broadcast_campaigns');
        Schema::dropIfExists('ai_training_sources');
        Schema::dropIfExists('ai_automations');
        Schema::dropIfExists('messages');
        Schema::dropIfExists('conversations');
        Schema::dropIfExists('contacts');
        Schema::dropIfExists('whatsapp_accounts');
        Schema::dropIfExists('workspace_user');
        Schema::dropIfExists('workspaces');
    }
};
