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
        Schema::create('schools', function (Blueprint $table) {
            $table->id();

            // Identitas Resmi
            $table->string('name');
            $table->string('npsn', 8)->index();
            $table->string('nss', 20)->nullable();
            $table->string('school_type', 10)->index();
            $table->string('status', 10);
            $table->string('accreditation', 10)->nullable();

            // Alamat Lengkap
            $table->text('address')->nullable();
            $table->string('village')->nullable();
            $table->string('district')->nullable();
            $table->string('city')->nullable();
            $table->string('province')->nullable();
            $table->string('postal_code', 10)->nullable();

            // Kontak
            $table->string('phone', 20)->nullable();
            $table->string('fax', 20)->nullable();
            $table->string('email')->nullable();
            $table->string('website')->nullable();

            // Pimpinan
            $table->string('principal_name')->nullable();
            $table->string('principal_nip', 30)->nullable();

            // Yayasan (relevan untuk Swasta)
            $table->string('foundation_name')->nullable();

            // Visual
            $table->string('logo_path')->nullable();

            // Status Sistem
            $table->boolean('is_active')->default(true)->index();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schools');
    }
};
