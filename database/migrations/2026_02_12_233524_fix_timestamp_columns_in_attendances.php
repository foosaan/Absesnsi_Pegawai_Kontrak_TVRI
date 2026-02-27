<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Fix: MySQL with explicit_defaults_for_timestamp=OFF auto-updates
     * the first TIMESTAMP column on row update. Change time columns
     * to DATETIME to prevent check_in_time from being overwritten on check-out.
     */
    public function up(): void
    {
        // MySQL-specific: change TIMESTAMP columns to DATETIME
        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE attendances MODIFY check_in_time DATETIME NULL DEFAULT NULL');
            DB::statement('ALTER TABLE attendances MODIFY check_out_time DATETIME NULL DEFAULT NULL');
            DB::statement('ALTER TABLE attendances MODIFY min_check_out_time DATETIME NULL DEFAULT NULL');
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE attendances MODIFY check_in_time TIMESTAMP NULL DEFAULT NULL');
            DB::statement('ALTER TABLE attendances MODIFY check_out_time TIMESTAMP NULL DEFAULT NULL');
            DB::statement('ALTER TABLE attendances MODIFY min_check_out_time TIMESTAMP NULL DEFAULT NULL');
        }
    }
};
