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
        Schema::create('jurusan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->string('kode', 20)->index();
            $table->string('nama', 150);
            $table->string('singkatan', 20)->nullable();
            $table->string('jenjang', 10)->default('SMK')->index(); // 'SMK' atau 'SMA'
            $table->string('bidang_keahlian', 100)->nullable();
            $table->string('program_keahlian', 100)->nullable();
            $table->string('kepala_jurusan', 100)->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->text('deskripsi')->nullable();
            $table->timestamps();

            $table->index(['school_id', 'is_active']);
            $table->index(['school_id', 'kode']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jurusan');
    }
};
