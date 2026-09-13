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
        Schema::create('semester', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tahun_ajaran_id')->constrained('tahun_ajaran')->cascadeOnDelete();
            $table->string('semester', 10)->index(); // 'ganjil', 'genap'
            $table->date('tanggal_mulai')->nullable()->index();
            $table->date('tanggal_selesai')->nullable()->index();
            $table->boolean('is_active')->default(false)->index();
            $table->text('keterangan')->nullable();
            $table->timestamps();

            $table->index(['tahun_ajaran_id', 'semester']);
            $table->index(['tahun_ajaran_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('semester');
    }
};
