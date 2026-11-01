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
        Schema::create('referensi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('referensi_id')
                ->nullable()
                ->constrained('referensi')
                ->cascadeOnDelete();
            $table->string('nama', 150);
            $table->string('kode', 50)->index();
            $table->boolean('is_active')->default(true)->index();
            $table->boolean('is_system')->default(false);
            $table->json('extra_attributes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('referensi');
    }
};
