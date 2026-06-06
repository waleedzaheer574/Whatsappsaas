<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ai_training_sources', function (Blueprint $table) {
            if (! Schema::hasColumn('ai_training_sources', 'content')) {
                $table->longText('content')->nullable()->after('type');
            }

            if (! Schema::hasColumn('ai_training_sources', 'source_url')) {
                $table->string('source_url')->nullable()->after('content');
            }

            if (! Schema::hasColumn('ai_training_sources', 'metadata')) {
                $table->json('metadata')->nullable()->after('trained_at');
            }
        });

        Schema::table('broadcast_campaigns', function (Blueprint $table) {
            if (! Schema::hasColumn('broadcast_campaigns', 'body')) {
                $table->text('body')->nullable()->after('name');
            }

            if (! Schema::hasColumn('broadcast_campaigns', 'audience_filter')) {
                $table->json('audience_filter')->nullable()->after('body');
            }

            if (! Schema::hasColumn('broadcast_campaigns', 'started_at')) {
                $table->timestamp('started_at')->nullable()->after('scheduled_at');
            }

            if (! Schema::hasColumn('broadcast_campaigns', 'completed_at')) {
                $table->timestamp('completed_at')->nullable()->after('started_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('broadcast_campaigns', function (Blueprint $table) {
            foreach (['completed_at', 'started_at', 'audience_filter', 'body'] as $column) {
                if (Schema::hasColumn('broadcast_campaigns', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('ai_training_sources', function (Blueprint $table) {
            foreach (['metadata', 'source_url', 'content'] as $column) {
                if (Schema::hasColumn('ai_training_sources', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
