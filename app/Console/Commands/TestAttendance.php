<?php

namespace App\Console\Commands;

use App\Models\Attendance;
use App\Models\Shift;
use App\Models\User;
use App\Services\AttendanceService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class TestAttendance extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'attendance:test 
                            {action=checkin : Action to perform (checkin/checkout/reset)}
                            {--user= : User ID or email}
                            {--time= : Custom time (HH:MM format)}
                            {--date= : Custom date (YYYY-MM-DD format)}
                            {--shift= : Shift ID to use}
                            {--list : List all users and shifts}';

    /**
     * The console command description.
     */
    protected $description = 'Test attendance logic with custom time simulation';

    protected AttendanceService $attendanceService;

    public function __construct(AttendanceService $attendanceService)
    {
        parent::__construct();
        $this->attendanceService = $attendanceService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // List mode
        if ($this->option('list')) {
            return $this->listData();
        }

        $action = $this->argument('action');

        // Validate user
        $userInput = $this->option('user');
        if (!$userInput) {
            $this->error('Please specify --user (ID or email)');
            $this->info('Example: php artisan attendance:test checkin --user=1 --time=08:30');
            return 1;
        }

        $user = is_numeric($userInput) 
            ? User::find($userInput) 
            : User::where('email', $userInput)->first();

        if (!$user) {
            $this->error("User not found: {$userInput}");
            return 1;
        }

        // Parse custom time
        $customTime = $this->option('time');
        $customDate = $this->option('date') ?? Carbon::today()->format('Y-m-d');
        
        if ($customTime) {
            $simulatedTime = Carbon::parse("{$customDate} {$customTime}");
        } else {
            $simulatedTime = Carbon::now();
        }

        $this->info("===========================================");
        $this->info("🧪 ATTENDANCE TEST SIMULATION");
        $this->info("===========================================");
        $this->info("User      : {$user->name} ({$user->email})");
        $this->info("Role      : {$user->role}");
        $this->info("Action    : {$action}");
        $this->info("Simulated : {$simulatedTime->format('d M Y H:i:s')}");
        $this->info("===========================================");

        switch ($action) {
            case 'checkin':
                return $this->simulateCheckIn($user, $simulatedTime);
            case 'checkout':
                return $this->simulateCheckOut($user, $simulatedTime);
            case 'reset':
                return $this->resetAttendance($user, $customDate);
            case 'status':
                return $this->showStatus($user, $customDate);
            default:
                $this->error("Unknown action: {$action}");
                $this->info("Available actions: checkin, checkout, reset, status");
                return 1;
        }
    }

    /**
     * Simulate check-in with custom time
     */
    protected function simulateCheckIn(User $user, Carbon $time)
    {
        // Check existing attendance
        $existing = Attendance::where('user_id', $user->id)
            ->whereDate('check_in_time', $time->toDateString())
            ->first();

        if ($existing) {
            $this->warn("⚠️  User already checked in today at {$existing->check_in_time->format('H:i')}");
            $this->warn("   Status: {$existing->status}");
            
            if ($this->confirm('Delete existing attendance and create new one?')) {
                $existing->delete();
            } else {
                return 1;
            }
        }

        // Get active shift
        $shiftId = $this->option('shift');
        $shift = $shiftId 
            ? Shift::find($shiftId) 
            : $this->attendanceService->getShiftForTime($time);

        if (!$shift) {
            $this->error("No active shift found for time: {$time->format('H:i')}");
            $this->info("Available shifts:");
            Shift::all()->each(function ($s) {
                $this->info("  [{$s->id}] {$s->name}: {$s->start_time} - {$s->end_time}");
            });
            return 1;
        }

        $this->info("Shift     : {$shift->name} ({$shift->start_time} - {$shift->end_time})");
        $this->info("Tolerance : {$shift->tolerance_minutes} minutes");

        // Calculate status - extract time portion if start_time is a datetime
        $startTimeString = $shift->start_time;
        if ($startTimeString instanceof Carbon) {
            $startTimeString = $startTimeString->format('H:i:s');
        } elseif (strlen($startTimeString) > 8) {
            // It's a datetime string, extract time portion
            $startTimeString = Carbon::parse($startTimeString)->format('H:i:s');
        }
        
        $shiftStart = Carbon::parse($time->format('Y-m-d') . ' ' . $startTimeString);
        $lateThreshold = $shiftStart->copy()->addMinutes($shift->tolerance_minutes);

        $isLate = $time->gt($lateThreshold);
        $status = $isLate ? 'late' : 'present';

        $this->newLine();
        $this->info("📊 CALCULATION:");
        $this->info("   Shift Start    : {$shiftStart->format('H:i')}");
        $this->info("   Late Threshold : {$lateThreshold->format('H:i')} (+{$shift->tolerance_minutes}min)");
        $this->info("   Check-in Time  : {$time->format('H:i')}");
        
        if ($isLate) {
            $lateMinutes = $time->diffInMinutes($lateThreshold);
            $this->error("   ❌ LATE by {$lateMinutes} minutes");
        } else {
            $this->info("   ✅ ON TIME");
        }

        // Create attendance record
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'shift_id' => $shift->id,
            'check_in_time' => $time,
            'status' => $status,
            'photo_path' => 'test/simulated_checkin.png',
            'latitude' => 0,
            'longitude' => 0,
        ]);

        $this->newLine();
        $this->info("✅ Attendance created:");
        $this->table(
            ['Field', 'Value'],
            [
                ['ID', $attendance->id],
                ['Check-in', $attendance->check_in_time->format('H:i:s')],
                ['Status', strtoupper($attendance->status)],
                ['Shift', $shift->name],
            ]
        );

        return 0;
    }

    /**
     * Simulate check-out with custom time
     */
    protected function simulateCheckOut(User $user, Carbon $time)
    {
        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('check_in_time', $time->toDateString())
            ->first();

        if (!$attendance) {
            $this->error("❌ No check-in found for today. Please check-in first.");
            return 1;
        }

        if ($attendance->check_out_time) {
            $this->warn("⚠️  Already checked out at {$attendance->check_out_time->format('H:i')}");
            if (!$this->confirm('Update check-out time?')) {
                return 1;
            }
        }

        $shift = $attendance->shift;
        $this->info("Shift     : {$shift->name} ({$shift->start_time} - {$shift->end_time})");

        // Calculate if checkout is valid
        $checkInTime = $attendance->check_in_time;
        $minWorkHours = 8; // Minimum work hours
        $minCheckOutTime = $checkInTime->copy()->addHours($minWorkHours);

        // Extract time portion from end_time
        $endTimeString = $shift->end_time;
        if ($endTimeString instanceof Carbon) {
            $endTimeString = $endTimeString->format('H:i:s');
        } elseif (strlen($endTimeString) > 8) {
            $endTimeString = Carbon::parse($endTimeString)->format('H:i:s');
        }
        
        $shiftEnd = Carbon::parse($time->format('Y-m-d') . ' ' . $endTimeString);
        
        // Handle overnight shift
        if ($shiftEnd->lt($checkInTime)) {
            $shiftEnd->addDay();
        }

        $this->newLine();
        $this->info("📊 CALCULATION:");
        $this->info("   Check-in Time     : {$checkInTime->format('H:i')}");
        $this->info("   Shift End         : {$shiftEnd->format('H:i')}");
        $this->info("   Min Work Hours    : {$minWorkHours}h");
        $this->info("   Earliest Checkout : {$minCheckOutTime->format('H:i')}");
        $this->info("   Checkout Time     : {$time->format('H:i')}");

        $workedMinutes = $time->diffInMinutes($checkInTime);
        $workedHours = floor($workedMinutes / 60);
        $workedMins = $workedMinutes % 60;

        $this->info("   Worked Duration   : {$workedHours}h {$workedMins}m");

        if ($time->lt($minCheckOutTime)) {
            $this->warn("   ⚠️  Early checkout (less than {$minWorkHours}h)");
        } else {
            $this->info("   ✅ Valid checkout");
        }

        // Update attendance
        $attendance->update([
            'check_out_time' => $time,
            'check_out_photo' => 'test/simulated_checkout.png',
        ]);

        $this->newLine();
        $this->info("✅ Check-out recorded:");
        $this->table(
            ['Field', 'Value'],
            [
                ['ID', $attendance->id],
                ['Check-in', $attendance->check_in_time->format('H:i')],
                ['Check-out', $time->format('H:i')],
                ['Status', strtoupper($attendance->status)],
                ['Duration', "{$workedHours}h {$workedMins}m"],
            ]
        );

        return 0;
    }

    /**
     * Reset attendance for a user on specific date
     */
    protected function resetAttendance(User $user, string $date)
    {
        $count = Attendance::where('user_id', $user->id)
            ->whereDate('check_in_time', $date)
            ->delete();

        $this->info("✅ Deleted {$count} attendance record(s) for {$user->name} on {$date}");
        return 0;
    }

    /**
     * Show attendance status for a user
     */
    protected function showStatus(User $user, string $date)
    {
        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('check_in_time', $date)
            ->with('shift')
            ->first();

        if (!$attendance) {
            $this->warn("No attendance record for {$user->name} on {$date}");
            return 0;
        }

        $this->table(
            ['Field', 'Value'],
            [
                ['Date', $date],
                ['Check-in', $attendance->check_in_time->format('H:i:s')],
                ['Check-out', $attendance->check_out_time?->format('H:i:s') ?? '-'],
                ['Status', strtoupper($attendance->status)],
                ['Shift', $attendance->shift->name ?? '-'],
            ]
        );

        return 0;
    }

    /**
     * List all users and shifts
     */
    protected function listData()
    {
        $this->info("\n📋 AVAILABLE USERS:");
        $this->table(
            ['ID', 'Name', 'Email', 'Role'],
            User::all(['id', 'name', 'email', 'role'])->toArray()
        );

        $this->info("\n⏰ AVAILABLE SHIFTS:");
        $this->table(
            ['ID', 'Name', 'Start', 'End', 'Tolerance'],
            Shift::all()->map(fn($s) => [
                'id' => $s->id,
                'name' => $s->name,
                'start' => substr($s->start_time, 0, 5),
                'end' => substr($s->end_time, 0, 5),
                'tolerance' => $s->tolerance_minutes . ' min',
            ])->toArray()
        );

        $this->info("\n📝 USAGE EXAMPLES:");
        $this->line("  php artisan attendance:test checkin --user=1 --time=08:00    # On time (shift 08:00)");
        $this->line("  php artisan attendance:test checkin --user=1 --time=09:00    # Late");
        $this->line("  php artisan attendance:test checkout --user=1 --time=17:00   # Checkout");
        $this->line("  php artisan attendance:test status --user=1                  # Check status");
        $this->line("  php artisan attendance:test reset --user=1                   # Reset today's data");
        $this->line("  php artisan attendance:test reset --user=1 --date=2026-02-01 # Reset specific date");

        return 0;
    }
}
