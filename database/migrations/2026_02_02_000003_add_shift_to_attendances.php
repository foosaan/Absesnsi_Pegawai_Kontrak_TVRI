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
        Schema::table('attendances', function (Blueprint $table) {
            // Add shift reference for shift-based attendance
            $table->foreignId('shift_id')->nullable()->after('user_id')->constrained()->nullOnDelete();
            
            // Add attendance type to distinguish normal vs shift
            $table->enum('attendance_type', ['normal', 'shift'])->default('normal')->after('shift_id');
            
            // Add minimum checkout time (calculated at check-in)
            $table->timestamp('min_check_out_time')->nullable()->after('check_out_time');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropForeign(['shift_id']);
            $table->dropColumn(['shift_id', 'attendance_type', 'min_check_out_time']);
        });
    }
};
