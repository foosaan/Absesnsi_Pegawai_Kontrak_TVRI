<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Menambahkan nilai 'umum' ke enum attendance_type di tabel users DAN attendances.
     */
    public function up(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            // Ubah enum di tabel users
            DB::statement("ALTER TABLE users MODIFY COLUMN attendance_type ENUM('normal', 'shift', 'umum') NOT NULL DEFAULT 'normal'");

            // Ubah enum di tabel attendances
            DB::statement("ALTER TABLE attendances MODIFY COLUMN attendance_type ENUM('normal', 'shift', 'umum') NOT NULL DEFAULT 'normal'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            // Revert attendances
            DB::statement("UPDATE attendances SET attendance_type = 'normal' WHERE attendance_type = 'umum'");
            DB::statement("ALTER TABLE attendances MODIFY COLUMN attendance_type ENUM('normal', 'shift') NOT NULL DEFAULT 'normal'");

            // Revert users
            DB::statement("UPDATE users SET attendance_type = 'normal' WHERE attendance_type = 'umum'");
            DB::statement("ALTER TABLE users MODIFY COLUMN attendance_type ENUM('normal', 'shift') NOT NULL DEFAULT 'normal'");
        }
    }
};
