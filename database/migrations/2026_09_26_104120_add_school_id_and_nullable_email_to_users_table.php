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
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('school_id')
                ->nullable()
                ->after('id')
                ->constrained('schools')
                ->nullOnDelete();
            $table->string('email')->nullable()->change();
            $table->index(['school_id', 'user_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['school_id', 'user_type']);
            $table->dropForeign(['school_id']);
            $table->dropColumn('school_id');
            $table->string('email')->nullable(false)->change();
        });
    }
};
