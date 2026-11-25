<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('tenant_nodes')) {
            return;
        }

        Schema::create('tenant_nodes', function (Blueprint $table) {
            $table->id();
            $table->string('node_code')->index();
            $table->string('school_name');
            $table->string('npsn', 30)->nullable()->index();
            $table->string('base_url')->nullable();
            $table->string('api_token_hash', 64)->index();
            $table->text('hmac_secret');
            $table->boolean('is_active')->default(true);
            $table->string('status', 30)->default('pending');
            $table->string('db_status', 30)->default('unknown');
            $table->unsignedInteger('db_latency_ms')->nullable();
            $table->unsignedInteger('active_users_count')->default(0);
            $table->unsignedInteger('policy_version')->default(1);
            $table->unsignedInteger('last_applied_policy_version')->default(0);
            $table->string('app_version', 50)->nullable();
            $table->string('last_migration')->nullable();
            $table->timestamp('last_heartbeat_at')->nullable()->index();
            $table->text('last_error_message')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenant_nodes');
    }
};
