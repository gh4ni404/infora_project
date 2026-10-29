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
        Schema::create('murids', function (Blueprint $table) {
            $table->id();

            // Tenancy & Relasi Entitas
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('kelas_id')->nullable()->constrained('kelas')->nullOnDelete();
            $table->string('angkatan', 20)->nullable()->index();

            // Nomor Registrasi & Identitas Pokok Siswa
            $table->string('nisn', 20)->nullable()->index();
            $table->string('nis', 30)->nullable()->index();
            $table->string('nik', 20)->nullable()->index();
            $table->string('nama_lengkap')->index();
            $table->enum('jenis_kelamin', ['L', 'P'])->index();
            $table->string('tempat_lahir', 100)->nullable();
            $table->date('tanggal_lahir')->nullable();
            $table->string('agama', 30)->nullable();

            // Kontak & Komunikasi
            $table->string('no_hp', 30)->nullable();
            $table->string('email', 100)->nullable();

            // Domisili Administratif (Standar Resmi Kemendagri 4-Tier)
            $table->foreignId('provinsi_id')->nullable()->constrained('provinsi')->nullOnDelete();
            $table->foreignId('kabupaten_id')->nullable()->constrained('kabupaten')->nullOnDelete();
            $table->foreignId('kecamatan_id')->nullable()->constrained('kecamatan')->nullOnDelete();
            $table->foreignId('kelurahan_id')->nullable()->constrained('kelurahan')->nullOnDelete();
            $table->text('alamat')->nullable();

            // Data Orang Tua / Wali Siswa
            $table->string('nama_ayah', 150)->nullable();
            $table->string('nama_ibu', 150)->nullable();
            $table->string('nama_wali', 150)->nullable();
            $table->string('no_hp_ortu', 30)->nullable();

            // Status & Berkas Pasfoto
            $table->string('foto_path')->nullable();
            $table->string('status', 20)->default('aktif')->index();
            $table->boolean('is_active')->default(true)->index();

            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('murids');
    }
};
