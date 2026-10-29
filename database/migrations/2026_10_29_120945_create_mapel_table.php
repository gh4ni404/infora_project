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
        Schema::create('mapel', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('jurusan_id')->nullable()->constrained('jurusan')->nullOnDelete();
            $table->string('kode', 20)->index();
            $table->string('nama', 150);
            $table->string('singkatan', 20)->nullable();
            $table->string('kelompok', 50)->default('Umum')->index();
            $table->unsignedTinyInteger('beban_jam')->default(2)->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->text('keterangan')->nullable();
            $table->timestamps();

            $table->index(['school_id', 'is_active']);
            $table->index(['school_id', 'kode']);
            $table->index(['school_id', 'kelompok']);
            $table->index(['school_id', 'jurusan_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mapel');
    }
};
