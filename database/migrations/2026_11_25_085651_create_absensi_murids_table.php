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
        Schema::create('absensi_murids', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('murid_id')->constrained('murids')->cascadeOnDelete();
            $table->foreignId('kelas_id')->nullable()->constrained('kelas')->nullOnDelete();
            $table->foreignId('tahun_ajaran_id')->nullable()->constrained('tahun_ajaran')->nullOnDelete();
            $table->foreignId('semester_id')->nullable()->constrained('semester')->nullOnDelete();
            $table->foreignId('face_profile_id')->nullable()->constrained('murid_face_profiles')->nullOnDelete();
            $table->date('tanggal')->index();
            $table->timestamp('jam_masuk')->nullable();
            $table->timestamp('jam_pulang')->nullable();
            $table->string('status', 20)->default('hadir')->index();
            $table->string('metode', 30)->default('face_recognition');
            $table->decimal('match_score', 5, 4)->nullable();
            $table->string('foto_masuk_path')->nullable();
            $table->string('foto_pulang_path')->nullable();
            $table->string('keterangan', 255)->nullable();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['school_id', 'murid_id', 'tanggal'], 'absensi_murids_school_murid_date_unique');
            $table->index(['school_id', 'tanggal', 'status'], 'absensi_murids_school_date_status_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('absensi_murids');
    }
};
