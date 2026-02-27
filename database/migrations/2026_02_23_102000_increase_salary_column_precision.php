<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Increase precision of salary monetary columns from decimal(12,2) to decimal(15,2)
     * to prevent "Numeric value out of range" errors during import.
     */
    public function up(): void
    {
        Schema::table('salaries', function (Blueprint $table) {
            $table->decimal('base_salary', 15, 2)->default(0)->change();
            $table->decimal('deductions', 15, 2)->default(0)->change();
            $table->decimal('final_salary', 15, 2)->default(0)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('salaries', function (Blueprint $table) {
            $table->decimal('base_salary', 12, 2)->default(0)->change();
            $table->decimal('deductions', 12, 2)->default(0)->change();
            $table->decimal('final_salary', 12, 2)->default(0)->change();
        });
    }
};
