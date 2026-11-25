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
        Schema::table('menus', function (Blueprint $table) {
            $table->string('sync_key')->nullable()->after('route_name');
            $table->boolean('is_master_enabled')->default(true)->after('is_active');
            $table->index('sync_key');
        });

        Schema::table('sub_menus', function (Blueprint $table) {
            $table->string('sync_key')->nullable()->after('route_name');
            $table->boolean('is_master_enabled')->default(true)->after('is_active');
            $table->index('sync_key');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('last_seen_at')->nullable()->after('is_active');
            $table->index('last_seen_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['last_seen_at']);
            $table->dropColumn('last_seen_at');
        });

        Schema::table('sub_menus', function (Blueprint $table) {
            $table->dropIndex(['sync_key']);
            $table->dropColumn(['sync_key', 'is_master_enabled']);
        });

        Schema::table('menus', function (Blueprint $table) {
            $table->dropIndex(['sync_key']);
            $table->dropColumn(['sync_key', 'is_master_enabled']);
        });
    }
};
