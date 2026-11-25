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
        foreach (['menus', 'sub_menus'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->unsignedTinyInteger('dev_progress')->default(0)->after('is_active');
                $table->string('dev_stage', 120)->nullable()->after('dev_progress');
                $table->string('dev_note', 255)->nullable()->after('dev_stage');
                $table->string('dev_version', 30)->nullable()->after('dev_note');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        foreach (['menus', 'sub_menus'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropColumn([
                    'dev_progress',
                    'dev_stage',
                    'dev_note',
                    'dev_version',
                ]);
            });
        }
    }
};
