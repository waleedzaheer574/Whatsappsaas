<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('teams')) {
            Schema::create('teams', function (Blueprint $table) {
                $table->id();
                $table->foreignId('workspace_id')->constrained()->cascadeOnDelete();
                $table->string('name');
                $table->text('description')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('roles')) {
            Schema::create('roles', function (Blueprint $table) {
                $table->id();
                $table->foreignId('workspace_id')->nullable()->constrained()->cascadeOnDelete();
                $table->string('name');
                $table->string('slug');
                $table->boolean('is_system')->default(false);
                $table->timestamps();
                $table->unique(['workspace_id', 'slug']);
            });
        }

        if (! Schema::hasTable('permissions')) {
            Schema::create('permissions', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('slug')->unique();
                $table->string('group')->default('general');
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('permission_role')) {
            Schema::create('permission_role', function (Blueprint $table) {
                $table->id();
                $table->foreignId('permission_id')->constrained()->cascadeOnDelete();
                $table->foreignId('role_id')->constrained()->cascadeOnDelete();
                $table->unique(['permission_id', 'role_id']);
            });
        }

        if (! Schema::hasTable('role_user')) {
            Schema::create('role_user', function (Blueprint $table) {
                $table->id();
                $table->foreignId('workspace_id')->constrained()->cascadeOnDelete();
                $table->foreignId('role_id')->constrained()->cascadeOnDelete();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->unique(['workspace_id', 'role_id', 'user_id']);
            });
        }

        if (! Schema::hasTable('team_user')) {
            Schema::create('team_user', function (Blueprint $table) {
                $table->id();
                $table->foreignId('team_id')->constrained()->cascadeOnDelete();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->string('role')->default('agent');
                $table->timestamps();
                $table->unique(['team_id', 'user_id']);
            });
        }

        if (! Schema::hasTable('leads')) {
            Schema::create('leads', function (Blueprint $table) {
                $table->id();
                $table->foreignId('workspace_id')->constrained()->cascadeOnDelete();
                $table->foreignId('contact_id')->nullable()->constrained()->nullOnDelete();
                $table->string('title');
                $table->string('stage')->default('new');
                $table->decimal('value', 12, 2)->default(0);
                $table->unsignedTinyInteger('score')->default(0);
                $table->timestamp('next_follow_up_at')->nullable();
                $table->json('custom_fields')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('deal_pipelines')) {
            Schema::create('deal_pipelines', function (Blueprint $table) {
                $table->id();
                $table->foreignId('workspace_id')->constrained()->cascadeOnDelete();
                $table->string('name');
                $table->json('stages');
                $table->boolean('is_default')->default(false);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('contact_notes')) {
            Schema::create('contact_notes', function (Blueprint $table) {
                $table->id();
                $table->foreignId('contact_id')->constrained()->cascadeOnDelete();
                $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
                $table->text('body');
                $table->boolean('is_internal')->default(true);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('labels')) {
            Schema::create('labels', function (Blueprint $table) {
                $table->id();
                $table->foreignId('workspace_id')->constrained()->cascadeOnDelete();
                $table->string('name');
                $table->string('color')->default('#7C3AED');
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('labelables')) {
            Schema::create('labelables', function (Blueprint $table) {
                $table->id();
                $table->foreignId('label_id')->constrained()->cascadeOnDelete();
                $table->morphs('labelable');
                $table->unique(['label_id', 'labelable_id', 'labelable_type']);
            });
        }

        if (! Schema::hasTable('conversation_notes')) {
            Schema::create('conversation_notes', function (Blueprint $table) {
                $table->id();
                $table->foreignId('conversation_id')->constrained()->cascadeOnDelete();
                $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
                $table->text('body');
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('message_media')) {
            Schema::create('message_media', function (Blueprint $table) {
                $table->id();
                $table->foreignId('message_id')->constrained()->cascadeOnDelete();
                $table->string('disk')->default('s3');
                $table->string('path');
                $table->string('mime_type')->nullable();
                $table->unsignedBigInteger('size')->default(0);
                $table->json('metadata')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('whatsapp_templates')) {
            Schema::create('whatsapp_templates', function (Blueprint $table) {
                $table->id();
                $table->foreignId('whatsapp_account_id')->constrained()->cascadeOnDelete();
                $table->string('name');
                $table->string('language')->default('en');
                $table->string('category')->default('utility');
                $table->string('status')->default('pending');
                $table->json('components');
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('webhooks')) {
            Schema::create('webhooks', function (Blueprint $table) {
                $table->id();
                $table->foreignId('workspace_id')->nullable()->constrained()->cascadeOnDelete();
                $table->string('provider');
                $table->string('event');
                $table->string('secret')->nullable();
                $table->string('url')->nullable();
                $table->boolean('is_active')->default(true);
                $table->json('headers')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('webhook_deliveries')) {
            Schema::create('webhook_deliveries', function (Blueprint $table) {
                $table->id();
                $table->foreignId('webhook_id')->nullable()->constrained()->nullOnDelete();
                $table->string('event');
                $table->string('status')->default('pending');
                $table->unsignedSmallInteger('attempts')->default(0);
                $table->json('payload');
                $table->text('response')->nullable();
                $table->timestamp('delivered_at')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('ai_prompts')) {
            Schema::create('ai_prompts', function (Blueprint $table) {
                $table->id();
                $table->foreignId('workspace_id')->constrained()->cascadeOnDelete();
                $table->string('name');
                $table->string('type')->default('reply');
                $table->string('tone')->default('friendly');
                $table->longText('system_prompt');
                $table->boolean('is_active')->default(true);
                $table->json('settings')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('ai_responses')) {
            Schema::create('ai_responses', function (Blueprint $table) {
                $table->id();
                $table->foreignId('workspace_id')->constrained()->cascadeOnDelete();
                $table->foreignId('conversation_id')->nullable()->constrained()->nullOnDelete();
                $table->string('provider')->default('openai');
                $table->string('model')->nullable();
                $table->longText('prompt');
                $table->longText('response')->nullable();
                $table->unsignedInteger('prompt_tokens')->default(0);
                $table->unsignedInteger('completion_tokens')->default(0);
                $table->decimal('cost', 10, 6)->default(0);
                $table->json('metadata')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('automation_triggers')) {
            Schema::create('automation_triggers', function (Blueprint $table) {
                $table->id();
                $table->foreignId('automation_id')->constrained('ai_automations')->cascadeOnDelete();
                $table->string('type');
                $table->json('config')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('automation_conditions')) {
            Schema::create('automation_conditions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('automation_id')->constrained('ai_automations')->cascadeOnDelete();
                $table->string('field');
                $table->string('operator');
                $table->string('value')->nullable();
                $table->json('config')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('automation_actions')) {
            Schema::create('automation_actions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('automation_id')->constrained('ai_automations')->cascadeOnDelete();
                $table->string('type');
                $table->unsignedInteger('sort_order')->default(0);
                $table->json('config')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('automation_logs')) {
            Schema::create('automation_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('automation_id')->nullable()->constrained('ai_automations')->nullOnDelete();
                $table->foreignId('conversation_id')->nullable()->constrained()->nullOnDelete();
                $table->string('status');
                $table->json('context')->nullable();
                $table->text('error')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('campaigns')) {
            Schema::create('campaigns', function (Blueprint $table) {
                $table->id();
                $table->foreignId('workspace_id')->constrained()->cascadeOnDelete();
                $table->string('name');
                $table->string('type')->default('broadcast');
                $table->string('status')->default('draft');
                $table->json('audience_filter')->nullable();
                $table->timestamp('scheduled_at')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('broadcasts')) {
            Schema::create('broadcasts', function (Blueprint $table) {
                $table->id();
                $table->foreignId('campaign_id')->constrained()->cascadeOnDelete();
                $table->foreignId('contact_id')->nullable()->constrained()->nullOnDelete();
                $table->string('status')->default('queued');
                $table->text('body');
                $table->json('variables')->nullable();
                $table->unsignedSmallInteger('attempts')->default(0);
                $table->timestamp('sent_at')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('csv_imports')) {
            Schema::create('csv_imports', function (Blueprint $table) {
                $table->id();
                $table->foreignId('workspace_id')->constrained()->cascadeOnDelete();
                $table->string('filename');
                $table->string('status')->default('pending');
                $table->unsignedInteger('total_rows')->default(0);
                $table->unsignedInteger('processed_rows')->default(0);
                $table->json('mapping')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('subscriptions')) {
            Schema::create('subscriptions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('workspace_id')->constrained()->cascadeOnDelete();
                $table->string('stripe_id')->nullable()->unique();
                $table->string('plan')->default('starter');
                $table->string('status')->default('trialing');
                $table->timestamp('trial_ends_at')->nullable();
                $table->timestamp('renews_at')->nullable();
                $table->timestamp('ends_at')->nullable();
                $table->json('limits')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('invoices')) {
            Schema::create('invoices', function (Blueprint $table) {
                $table->id();
                $table->foreignId('workspace_id')->constrained()->cascadeOnDelete();
                $table->string('stripe_invoice_id')->nullable()->unique();
                $table->string('number')->nullable();
                $table->decimal('amount_due', 12, 2)->default(0);
                $table->decimal('amount_paid', 12, 2)->default(0);
                $table->string('currency', 3)->default('usd');
                $table->string('status')->default('draft');
                $table->string('hosted_url')->nullable();
                $table->timestamp('paid_at')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('usage_records')) {
            Schema::create('usage_records', function (Blueprint $table) {
                $table->id();
                $table->foreignId('workspace_id')->constrained()->cascadeOnDelete();
                $table->string('metric');
                $table->unsignedBigInteger('quantity')->default(0);
                $table->date('period_start');
                $table->date('period_end');
                $table->timestamps();
                $table->index(['workspace_id', 'metric', 'period_start']);
            });
        }

        if (! Schema::hasTable('api_keys')) {
            Schema::create('api_keys', function (Blueprint $table) {
                $table->id();
                $table->foreignId('workspace_id')->constrained()->cascadeOnDelete();
                $table->string('name');
                $table->string('token_hash')->unique();
                $table->json('abilities')->nullable();
                $table->timestamp('last_used_at')->nullable();
                $table->timestamp('expires_at')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('connected_integrations')) {
            Schema::create('connected_integrations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('workspace_id')->constrained()->cascadeOnDelete();
                $table->string('provider');
                $table->string('status')->default('connected');
                $table->json('credentials')->nullable();
                $table->json('settings')->nullable();
                $table->timestamp('last_synced_at')->nullable();
                $table->timestamps();
                $table->unique(['workspace_id', 'provider']);
            });
        }

        if (! Schema::hasTable('security_events')) {
            Schema::create('security_events', function (Blueprint $table) {
                $table->id();
                $table->foreignId('workspace_id')->nullable()->constrained()->nullOnDelete();
                $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
                $table->string('type');
                $table->ipAddress('ip_address')->nullable();
                $table->text('user_agent')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('devices')) {
            Schema::create('devices', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->string('name');
                $table->ipAddress('ip_address')->nullable();
                $table->text('user_agent')->nullable();
                $table->timestamp('last_seen_at')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        foreach ([
            'devices', 'security_events', 'connected_integrations', 'api_keys', 'usage_records', 'invoices',
            'subscriptions', 'csv_imports', 'broadcasts', 'campaigns', 'automation_logs', 'automation_actions',
            'automation_conditions', 'automation_triggers', 'ai_responses', 'ai_prompts', 'webhook_deliveries',
            'webhooks', 'whatsapp_templates', 'message_media', 'conversation_notes', 'labelables', 'labels',
            'contact_notes', 'deal_pipelines', 'leads', 'team_user', 'role_user', 'permission_role',
            'permissions', 'roles', 'teams',
        ] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
