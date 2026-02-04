<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Shift;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AttendanceService
{
    const WORK_DURATION_HOURS = 8;

    /**
     * Get the applicable shift for a user based on their attendance type and current time
     */
    public function getApplicableShift(User $user, ?Carbon $time = null): ?Shift
    {
        $time = $time ?? Carbon::now();

        // Use attendance_type to determine shift type
        if ($user->isNormalAttendance()) {
            return Shift::getNormalShift();
        }

        if ($user->isShiftAttendance()) {
            return $this->getShiftForTime($time);
        }

        // Default to normal shift
        return Shift::getNormalShift();
    }

    /**
     * Get shift based on current time for Satpam
     */
    public function getShiftForTime(Carbon $time): ?Shift
    {
        $timeString = $time->format('H:i:s');
        
        // Handle Shift 1 (00:00-08:00)
        if ($timeString >= '00:00:00' && $timeString < '08:00:00') {
            return Shift::where('name', 'Shift 1')->first();
        }
        
        // Handle Shift 2 (08:00-16:00)
        if ($timeString >= '08:00:00' && $timeString < '16:00:00') {
            return Shift::where('name', 'Shift 2')->first();
        }
        
        // Handle Shift 3 (16:00-24:00)
        if ($timeString >= '16:00:00' && $timeString <= '23:59:59') {
            return Shift::where('name', 'Shift 3')->first();
        }

        return null;
    }

    /**
     * Calculate minimum checkout time
     * - If ON TIME: check_in + 8 hours
     * - If LATE: use shift end time (normal checkout)
     */
    public function calculateMinCheckOutTime(Carbon $checkInTime, Shift $shift, bool $isLate): Carbon
    {
        if ($isLate) {
            // Jika terlambat, checkout mengikuti jam normal shift
            $endTimeString = $shift->end_time;
            if ($endTimeString instanceof Carbon) {
                $endTimeString = $endTimeString->format('H:i:s');
            }
            
            // Set checkout ke jam end shift di hari yang sama dengan check-in
            return $checkInTime->copy()->setTimeFromTimeString($endTimeString);
        }
        
        // Jika tepat waktu, checkout = check-in + 8 jam
        return $checkInTime->copy()->addHours(self::WORK_DURATION_HOURS);
    }

    /**
     * Check if user is late based on their shift
     */
    public function isLate(Shift $shift, Carbon $checkInTime): bool
    {
        // Parse shift start time - handle both string and Carbon formats
        $startTimeString = $shift->start_time;
        if ($startTimeString instanceof Carbon) {
            $startTimeString = $startTimeString->format('H:i:s');
        }
        
        $shiftStart = Carbon::createFromFormat('H:i:s', $startTimeString);
        $toleranceEnd = $shiftStart->copy()->addMinutes($shift->tolerance_minutes);
        
        // Compare only the time portion
        $checkInTimeOnly = Carbon::createFromFormat('H:i:s', $checkInTime->format('H:i:s'));
        
        return $checkInTimeOnly->gt($toleranceEnd);
    }

    /**
     * Check if user can check-in now
     * Returns ['can' => bool, 'message' => string, 'shift' => ?Shift]
     */
    public function canCheckIn(User $user): array
    {
        $now = Carbon::now();
        $today = Carbon::today();

        // Check if already checked in today
        $existing = Attendance::where('user_id', $user->id)
            ->whereDate('check_in_time', $today)
            ->first();

        if ($existing) {
            return [
                'can' => false,
                'message' => 'Anda sudah absen masuk hari ini.',
                'shift' => null,
            ];
        }

        $shift = $this->getApplicableShift($user, $now);

        if (!$shift) {
            return [
                'can' => false,
                'message' => 'Tidak ada shift yang berlaku untuk waktu ini.',
                'shift' => null,
            ];
        }

        // For normal attendance users, check if within working hours
        if ($user->isNormalAttendance()) {
            $startTimeString = $shift->start_time;
            if ($startTimeString instanceof Carbon) {
                $startTimeString = $startTimeString->format('H:i:s');
            }
            $startTime = Carbon::createFromFormat('H:i:s', $startTimeString);
            
            if ($now->format('H:i:s') < $startTimeString) {
                return [
                    'can' => false,
                    'message' => 'Waktu absen masuk belum dimulai (mulai jam ' . $startTime->format('H:i') . ').',
                    'shift' => $shift,
                ];
            }
        }

        $isLate = $this->isLate($shift, $now);
        $message = $isLate 
            ? 'Anda terlambat! Silakan lakukan Absen Masuk (status: Terlambat).'
            : 'Silakan lakukan Absen Masuk.';

        return [
            'can' => true,
            'message' => $message,
            'shift' => $shift,
            'is_late' => $isLate,
        ];
    }

    /**
     * Check if user can check-out now
     * Returns ['can' => bool, 'message' => string, 'remaining_minutes' => int]
     */
    public function canCheckOut(User $user): array
    {
        $today = Carbon::today();

        // Find today's attendance
        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('check_in_time', $today)
            ->first();

        if (!$attendance) {
            return [
                'can' => false,
                'message' => 'Anda belum absen masuk hari ini.',
                'remaining_minutes' => 0,
                'attendance' => null,
            ];
        }

        if ($attendance->check_out_time) {
            return [
                'can' => false,
                'message' => 'Anda sudah absen pulang hari ini.',
                'remaining_minutes' => 0,
                'attendance' => $attendance,
            ];
        }

        // Check minimum work duration
        if (!$attendance->canCheckOut()) {
            $remaining = $attendance->getRemainingMinutes();
            $hours = floor($remaining / 60);
            $mins = $remaining % 60;
            
            return [
                'can' => false,
                'message' => "Anda belum bisa absen pulang. Sisa waktu kerja: {$hours} jam {$mins} menit.",
                'remaining_minutes' => $remaining,
                'attendance' => $attendance,
            ];
        }

        return [
            'can' => true,
            'message' => 'Silakan lakukan Absen Pulang.',
            'remaining_minutes' => 0,
            'attendance' => $attendance,
        ];
    }

    /**
     * Process check-in for user
     */
    public function processCheckIn(User $user, array $data): Attendance
    {
        $now = Carbon::now();
        $shift = $this->getApplicableShift($user, $now);
        $isLate = $this->isLate($shift, $now);
        $minCheckOutTime = $this->calculateMinCheckOutTime($now, $shift, $isLate);

        return Attendance::create([
            'user_id' => $user->id,
            'shift_id' => $shift->id,
            'attendance_type' => $user->isNormalAttendance() ? 'normal' : 'shift',
            'photo_path' => $data['photo_path'],
            'check_in_time' => $now,
            'min_check_out_time' => $minCheckOutTime,
            'latitude' => $data['latitude'],
            'longitude' => $data['longitude'],
            'status' => $isLate ? 'late' : 'present',
        ]);
    }

    /**
     * Process check-out for user
     */
    public function processCheckOut(Attendance $attendance, array $data): Attendance
    {
        $attendance->update([
            'check_out_photo_path' => $data['photo_path'],
            'check_out_time' => now(),
            'check_out_latitude' => $data['latitude'],
            'check_out_longitude' => $data['longitude'],
        ]);

        return $attendance;
    }
}
