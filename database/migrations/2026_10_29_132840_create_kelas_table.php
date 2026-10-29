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
        Schema::create('kelas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('tahun_ajaran_id')->constrained('tahun_ajaran')->cascadeOnDelete();
            $table->foreignId('tingkatan_id')->constrained('tingkatan')->cascadeOnDelete();
            $table->foreignId('jurusan_id')->nullable()->constrained('jurusan')->nullOnDelete();
            $table->foreignId('wali_kelas_id')->nullable()->constrained('gtks')->nullOnDelete();

            $table->string('nama', 100);
            $table->string('kode', 50)->nullable();
            $table->string('ruangan', 100)->nullable();
            $table->unsignedSmallInteger('kapasitas')->default(36);
            $table->boolean('is_active')->default(true)->index();
            $table->text('keterangan')->nullable();
            $table->timestamps();

            $table->unique(['school_id', 'tahun_ajaran_id', 'nama'], 'kelas_school_ta_nama_unique');
            $table->index(['school_id', 'tahun_ajaran_id', 'is_active']);
            $table->index(['tingkatan_id', 'jurusan_id']);
            $table->index(['school_id', 'wali_kelas_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kelas');
    }
};
