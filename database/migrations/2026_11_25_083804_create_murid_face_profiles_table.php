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
        Schema::create('murid_face_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('murid_id')->constrained('murids')->cascadeOnDelete();
            $table->string('label', 60)->default('Tampak Depan');
            $table->string('foto_path');
            $table->json('face_descriptor');
            $table->decimal('confidence', 5, 4)->nullable();
            $table->string('model_version', 60)->default('vladmandic-face-api-tiny-128d');
            $table->boolean('is_active')->default(true)->index();
            $table->foreignId('registered_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['school_id', 'murid_id', 'is_active'], 'murid_face_profiles_school_murid_active_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('murid_face_profiles');
    }
};
