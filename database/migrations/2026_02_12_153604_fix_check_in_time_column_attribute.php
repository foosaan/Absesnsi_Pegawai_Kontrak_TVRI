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
        // MySQL-specific: change TIMESTAMP to allow NULL
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE attendances MODIFY COLUMN check_in_time TIMESTAMP NULL DEFAULT NULL");
        }
    }

    public function down(): void
    {
        // Kembalikan ke format timestamp (jika perlu rollback)
    }
};
