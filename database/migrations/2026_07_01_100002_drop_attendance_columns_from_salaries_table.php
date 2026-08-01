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
        Schema::table('salaries', function (Blueprint $table) {
            $table->dropColumn([
                'total_work_days',
                'total_late_days',
                'total_absent_days',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('salaries', function (Blueprint $table) {
            $table->integer('total_work_days')->default(0);
            $table->integer('total_late_days')->default(0);
            $table->integer('total_absent_days')->default(0);
        });
    }
};
