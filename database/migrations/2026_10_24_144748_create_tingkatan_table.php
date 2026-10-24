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
        Schema::create('tingkatan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->string('kode', 20)->index();
            $table->string('nama', 100);
            $table->unsignedTinyInteger('tingkat_angka')->index();
            $table->string('fase', 20)->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->text('keterangan')->nullable();
            $table->timestamps();

            $table->unique(['school_id', 'kode']);
            $table->unique(['school_id', 'tingkat_angka']);
            $table->index(['school_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tingkatan');
    }
};
