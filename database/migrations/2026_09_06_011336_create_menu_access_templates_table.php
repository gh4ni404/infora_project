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
        Schema::create('menu_access_templates', function (Blueprint $table) {
            $table->id();
            $table->string('role_key');
            $table->string('role_name');
            $table->string('role_category')->default('guru');
            $table->foreignId('menu_id')->nullable()->constrained('menus')->cascadeOnDelete();
            $table->foreignId('sub_menu_id')->nullable()->constrained('sub_menus')->cascadeOnDelete();
            $table->boolean('can_view')->default(false);
            $table->boolean('can_create')->default(false);
            $table->boolean('can_edit')->default(false);
            $table->boolean('can_delete')->default(false);
            $table->timestamps();

            $table->index(['role_key', 'menu_id']);
            $table->index(['role_key', 'sub_menu_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('menu_access_templates');
    }
};
