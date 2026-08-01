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
        if (Schema::hasColumn('employee_profiles', 'gaji_pokok')) {
            Schema::table('employee_profiles', function (Blueprint $table) {
                $table->dropColumn('gaji_pokok');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_profiles', function (Blueprint $table) {
            $table->decimal('gaji_pokok', 15, 2)->default(0)->after('nik');
        });
    }
};
