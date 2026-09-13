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
        Schema::create('kalender_akademik', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->string('judul_kegiatan', 150);
            $table->date('tanggal_mulai')->index();
            $table->date('tanggal_selesai')->index();
            $table->string('tahun_ajaran', 20)->index();
            $table->string('semester', 10)->index(); // 'ganjil', 'genap'
            $table->string('kategori', 50)->index(); // KBM Efektif, Ujian/Asesmen, Libur Nasional, Libur Semester, Kegiatan Sekolah, Khusus SMK
            $table->string('warna', 20)->default('blue'); // blue, emerald, amber, rose, purple, indigo
            $table->boolean('libur_kbm')->default(false)->index();
            $table->text('keterangan')->nullable();
            $table->timestamps();

            $table->index(['school_id', 'tahun_ajaran', 'semester']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kalender_akademik');
    }
};
