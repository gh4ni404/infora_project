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
            if (! Schema::hasTable($tableName)) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                if (! Schema::hasColumn($tableName, 'dev_progress')) {
                    $table->unsignedTinyInteger('dev_progress')->default(0)->after('is_active');
                }
                if (! Schema::hasColumn($tableName, 'dev_stage')) {
                    $table->string('dev_stage', 120)->nullable()->after('dev_progress');
                }
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
