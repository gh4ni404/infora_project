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
        Schema::create('gtks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('nama_lengkap');
            $table->string('gelar_depan', 50)->nullable();
            $table->string('gelar_belakang', 50)->nullable();
            $table->enum('jenis_kelamin', ['L', 'P']);
            $table->string('nik', 20)->nullable()->index();
            $table->string('nip', 30)->nullable()->index();
            $table->string('nuptk', 30)->nullable()->index();
            $table->json('jenis_ptk');
            $table->string('status_kepegawaian', 50)->index();
            $table->string('tempat_lahir', 100)->nullable();
            $table->date('tanggal_lahir')->nullable();
            $table->string('agama', 30)->nullable();
            $table->string('no_hp', 30)->nullable();
            $table->string('email', 100)->nullable();
            $table->foreignId('provinsi_id')->nullable()->constrained('provinsi')->nullOnDelete();
            $table->foreignId('kabupaten_id')->nullable()->constrained('kabupaten')->nullOnDelete();
            $table->foreignId('kecamatan_id')->nullable()->constrained('kecamatan')->nullOnDelete();
            $table->foreignId('kelurahan_id')->nullable()->constrained('kelurahan')->nullOnDelete();
            $table->text('alamat')->nullable();
            $table->string('pendidikan_terakhir', 50)->nullable();
            $table->string('jurusan_pendidikan', 100)->nullable();
            $table->string('foto_path')->nullable();
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
        Schema::dropIfExists('gtks');
    }
};
