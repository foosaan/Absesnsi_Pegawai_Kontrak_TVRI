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
        Schema::table('users', function (Blueprint $table) {
            // Add employee_type column: 'ob' or 'satpam'
            // Note: admin users will have null employee_type
            $table->enum('employee_type', ['ob', 'satpam'])->nullable()->after('role');
        });

        // Migrate existing 'user' role to 'ob' by default
        \Illuminate\Support\Facades\DB::table('users')
            ->where('role', 'user')
            ->update(['employee_type' => 'ob']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('employee_type');
        });
    }
};
