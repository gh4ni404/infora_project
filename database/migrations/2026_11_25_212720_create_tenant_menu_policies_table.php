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
        Schema::create('tenant_menu_policies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_node_id')->constrained('tenant_nodes')->cascadeOnDelete();
            $table->string('target_type', 20);
            $table->string('sync_key');
            $table->string('label_snapshot');
            $table->boolean('is_allowed')->default(true);
            $table->timestamps();

            $table->index(['tenant_node_id', 'target_type', 'sync_key'], 'tenant_menu_policies_lookup_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenant_menu_policies');
    }
};
