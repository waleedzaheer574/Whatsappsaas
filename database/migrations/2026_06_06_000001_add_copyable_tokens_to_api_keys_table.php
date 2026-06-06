<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('api_keys', function (Blueprint $table) {
            if (! Schema::hasColumn('api_keys', 'encrypted_token')) {
                $table->text('encrypted_token')->nullable()->after('token_hash');
            }

            if (! Schema::hasColumn('api_keys', 'token_preview')) {
                $table->string('token_preview')->nullable()->after('encrypted_token');
            }
        });
    }

    public function down(): void
    {
        Schema::table('api_keys', function (Blueprint $table) {
            if (Schema::hasColumn('api_keys', 'token_preview')) {
                $table->dropColumn('token_preview');
            }

            if (Schema::hasColumn('api_keys', 'encrypted_token')) {
                $table->dropColumn('encrypted_token');
            }
        });
    }
};
