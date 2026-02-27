<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dateTime('max_check_out_time')->nullable()->after('min_check_out_time');
        });

        // Backfill max_check_out_time for existing records: shift end_time + 3 hours
        $attendances = \App\Models\Attendance::with('shift')->whereNull('max_check_out_time')->get();
        foreach ($attendances as $attendance) {
            if ($attendance->shift && $attendance->check_in_time) {
                $endTimeString = $attendance->shift->end_time;
                if ($endTimeString instanceof \Carbon\Carbon) {
                    $endTimeString = $endTimeString->format('H:i:s');
                }
                $maxCheckOut = $attendance->check_in_time->copy()->setTimeFromTimeString($endTimeString)->addHours(3);
                $attendance->update(['max_check_out_time' => $maxCheckOut]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropColumn('max_check_out_time');
        });
    }
};
