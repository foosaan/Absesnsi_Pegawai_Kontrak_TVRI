<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Pastikan data NULL disanitasi terlebih dahulu agar tidak memicu SQL Error saat ALTER
        DB::table('users')->whereNull('nip')->orWhere('nip', '')->update(['nip' => '199001012025041001']);
        DB::table('users')->whereNull('role')->update(['role' => 'user']);
        
        if (Schema::hasTable('employee_profiles')) {
            DB::table('employee_profiles')->whereNull('nik')->update(['nik' => '1234567890123456']);
        }

        // Ubah kolom menjadi NOT NULL di database
        Schema::table('users', function (Blueprint $table) {
            $table->string('nip', 18)->nullable(false)->change();
            $table->string('role')->nullable(false)->change();
        });

        Schema::table('employee_profiles', function (Blueprint $table) {
            $table->string('nik', 16)->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('nip', 18)->nullable()->change();
            $table->string('role')->nullable()->change();
        });

        Schema::table('employee_profiles', function (Blueprint $table) {
            $table->string('nik', 16)->nullable()->change();
        });
    }
};
