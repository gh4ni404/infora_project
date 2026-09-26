<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('users')
            ->where('user_type', 'siswa')
            ->update(['user_type' => 'murid']);

        DB::table('menu_access_templates')
            ->where('role_category', 'siswa')
            ->update([
                'role_category' => 'murid',
                'role_key' => DB::raw("REPLACE(role_key, 'siswa_', 'murid_')"),
                'role_name' => DB::raw("REPLACE(role_name, 'Siswa ', 'Murid ')"),
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('users')
            ->where('user_type', 'murid')
            ->update(['user_type' => 'siswa']);

        DB::table('menu_access_templates')
            ->where('role_category', 'murid')
            ->update([
                'role_category' => 'siswa',
                'role_key' => DB::raw("REPLACE(role_key, 'murid_', 'siswa_')"),
                'role_name' => DB::raw("REPLACE(role_name, 'Murid ', 'Siswa ')"),
            ]);
    }
};
