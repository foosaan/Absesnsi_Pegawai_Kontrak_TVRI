<?php

namespace App\Console\Commands;

use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Console\Command;

class MarkLeftAttendance extends Command
{
    protected $signature = 'attendance:mark-left';
    protected $description = 'Mark attendance as "left" (meninggalkan kantor) for employees who did not check out within the allowed time';

    public function handle(): int
    {
        $now = Carbon::now();

        // Find attendance records where:
        // 1. No check-out time recorded
        // 2. Status is not already 'left'
        // 3. max_check_out_time has passed
        $overdueAttendances = Attendance::whereNull('check_out_time')
            ->whereNotNull('max_check_out_time')
            ->where('max_check_out_time', '<', $now)
            ->where('status', '!=', 'left')
            ->where('status', '!=', 'cuti')
            ->get();

        $count = 0;
        foreach ($overdueAttendances as $attendance) {
            $attendance->update(['status' => 'left']);
            $count++;
        }

        if ($count > 0) {
            $this->info("Marked {$count} attendance record(s) as 'Meninggalkan Kantor'.");
        } else {
            $this->info('No overdue attendance records found.');
        }

        return self::SUCCESS;
    }
}
