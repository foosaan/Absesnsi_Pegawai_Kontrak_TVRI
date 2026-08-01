<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->date('work_date')->nullable()->after('check_in_time');
            $table->index('work_date');
        });

        // Backfill: isi work_date dari check_in_time untuk data yang sudah ada
        \DB::statement("UPDATE attendances SET work_date = DATE(check_in_time) WHERE work_date IS NULL AND check_in_time IS NOT NULL");
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropColumn('work_date');
        });
    }
};
