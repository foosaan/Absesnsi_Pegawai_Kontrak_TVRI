<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\BusinessTrip;
use App\Models\Leave;
use App\Models\Shift;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AttendanceService
{
    const WORK_DURATION_HOURS = 8;
    const MAX_CHECKOUT_HOURS = 3; // Hours after shift ends to allow checkout

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
     * Get shift based on current time — reads from DB dynamically
     */
    public function getShiftForTime(Carbon $time): ?Shift
    {
        $timeString = $time->format('H:i:s');
        
        // Try normal range first (start_time < end_time, e.g. 08:00-16:00)
        $shift = Shift::where('type', 'shift')
            ->whereRaw('start_time <= ? AND end_time > ?', [$timeString, $timeString])
            ->first();
        
        if ($shift) {
            return $shift;
        }
        
        // Handle midnight-crossing shifts (start_time > end_time, e.g. 22:00-06:00)
        $shift = Shift::where('type', 'shift')
            ->whereRaw('start_time > end_time')
            ->where(function ($query) use ($timeString) {
                $query->whereRaw('? >= start_time', [$timeString])
                      ->orWhereRaw('? < end_time', [$timeString]);
            })
            ->first();
        
        return $shift;
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
            
            $minCheckOut = $checkInTime->copy()->setTimeFromTimeString($endTimeString);
            
            // Fix midnight crossing: if end time is before check-in, it means next day
            if ($minCheckOut->lte($checkInTime)) {
                $minCheckOut->addDay();
            }
            
            return $minCheckOut;
        }
        
        // Jika tepat waktu, checkout = check-in + 8 jam
        return $checkInTime->copy()->addHours(self::WORK_DURATION_HOURS);
    }

    /**
     * Calculate maximum checkout time (shift end + 3 hours)
     * After this time, status becomes 'left' (Meninggalkan Kantor)
     */
    public function calculateMaxCheckOutTime(Carbon $checkInTime, Shift $shift): Carbon
    {
        $endTimeString = $shift->end_time;
        if ($endTimeString instanceof Carbon) {
            $endTimeString = $endTimeString->format('H:i:s');
        }
        
        $maxCheckOut = $checkInTime->copy()->setTimeFromTimeString($endTimeString);
        
        // Fix midnight crossing: if end time is before check-in, it means next day
        if ($maxCheckOut->lte($checkInTime)) {
            $maxCheckOut->addDay();
        }
        
        return $maxCheckOut->addHours(self::MAX_CHECKOUT_HOURS);
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
            $message = $existing->status === 'cuti'
                ? 'Anda sedang cuti hari ini.'
                : 'Anda sudah absen masuk hari ini.';
            return [
                'can' => false,
                'message' => $message,
                'shift' => null,
            ];
        }

        // Check if user has approved leave today
        $activeLeave = Leave::where('user_id', $user->id)
            ->where('status', 'approved')
            ->where('start_date', '<=', $today)
            ->where('end_date', '>=', $today)
            ->first();

        if ($activeLeave) {
            return [
                'can' => false,
                'message' => 'Anda sedang cuti (' . $activeLeave->type_label . ') sampai ' . $activeLeave->end_date->format('d M Y') . '.',
                'shift' => null,
            ];
        }

        // Check if user has approved business trip today
        $activeTrip = BusinessTrip::where('user_id', $user->id)
            ->where('status', 'approved')
            ->where('start_date', '<=', $today)
            ->where('end_date', '>=', $today)
            ->first();

        if ($activeTrip) {
            return [
                'can' => false,
                'message' => 'Anda sedang dinas luar ke ' . $activeTrip->destination . ' sampai ' . $activeTrip->end_date->format('d M Y') . '.',
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

        // Check if within working hours (applies to both normal and shift users)
        $startTimeString = $shift->start_time;
        if ($startTimeString instanceof Carbon) {
            $startTimeString = $startTimeString->format('H:i:s');
        }
        $startTime = Carbon::createFromFormat('H:i:s', $startTimeString);
        
        // Allow check-in up to 1 hour before shift starts
        $earlyTolerance = $startTime->copy()->subHour();
        $nowTimeOnly = Carbon::createFromFormat('H:i:s', $now->format('H:i:s'));
        
        if ($nowTimeOnly->lt($earlyTolerance)) {
            return [
                'can' => false,
                'message' => 'Waktu absen masuk belum dimulai (mulai jam ' . $startTime->format('H:i') . ', toleransi awal jam ' . $earlyTolerance->format('H:i') . ').',
                'shift' => $shift,
            ];
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

        // Check if status is 'left' (meninggalkan kantor)
        if ($attendance->status === 'left') {
            return [
                'can' => false,
                'message' => 'Batas waktu absen pulang telah terlewat. Status: Meninggalkan Kantor.',
                'remaining_minutes' => 0,
                'attendance' => $attendance,
            ];
        }

        // Check if max checkout time has passed
        if ($attendance->max_check_out_time && now()->gt($attendance->max_check_out_time)) {
            // Auto-mark as left
            $attendance->update(['status' => 'left']);
            return [
                'can' => false,
                'message' => 'Batas waktu absen pulang telah terlewat. Status: Meninggalkan Kantor.',
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
        $maxCheckOutTime = $this->calculateMaxCheckOutTime($now, $shift);

        // Prevent race condition: re-check inside transaction
        return DB::transaction(function () use ($user, $shift, $data, $now, $isLate, $minCheckOutTime, $maxCheckOutTime) {
            // Double-check no existing attendance today
            $existing = Attendance::where('user_id', $user->id)
                ->whereDate('check_in_time', $now->toDateString())
                ->lockForUpdate()
                ->first();
            
            if ($existing) {
                throw new \Exception('Anda sudah absen masuk hari ini.');
            }

            return Attendance::create([
                'user_id' => $user->id,
                'shift_id' => $shift->id,
                'attendance_type' => $user->isNormalAttendance() ? 'normal' : 'shift',
                'photo_path' => $data['photo_path'],
                'check_in_time' => $now,
                'min_check_out_time' => $minCheckOutTime,
                'max_check_out_time' => $maxCheckOutTime,
                'latitude' => $data['latitude'],
                'longitude' => $data['longitude'],
                'location_accuracy' => $data['location_accuracy'] ?? null,
                'is_mock_location' => $data['is_mock_location'] ?? false,
                'status' => $isLate ? 'late' : 'present',
            ]);
        });
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
            'check_out_location_accuracy' => $data['check_out_location_accuracy'] ?? null,
            'is_mock_location' => $data['is_mock_location'] ?? $attendance->is_mock_location,
        ]);

        return $attendance;
    }

    /**
     * Create attendance records for each weekday in a leave period
     */
    public function createLeaveAttendances(Leave $leave): int
    {
        $user = $leave->user;
        $shift = $this->getApplicableShift($user);
        $startDate = $leave->start_date->copy();
        $endDate = $leave->end_date->copy();
        $count = 0;

        while ($startDate->lte($endDate)) {
            // Skip weekends (Saturday=6, Sunday=0)
            if ($startDate->isWeekday()) {
                // Check if attendance already exists for this day
                $exists = Attendance::where('user_id', $user->id)
                    ->whereDate('check_in_time', $startDate)
                    ->exists();

                if (!$exists) {
                    // Create attendance record with 'cuti' status
                    Attendance::create([
                        'user_id' => $user->id,
                        'leave_id' => $leave->id,
                        'shift_id' => $shift?->id,
                        'attendance_type' => $user->isNormalAttendance() ? 'normal' : 'shift',
                        'photo_path' => 'cuti',
                        'check_in_time' => $startDate->copy()->setTimeFromTimeString('00:00:00'),
                        'check_out_time' => $startDate->copy()->setTimeFromTimeString('00:00:00'),
                        'latitude' => 0,
                        'longitude' => 0,
                        'status' => 'cuti',
                    ]);
                    $count++;
                }
            }
            $startDate->addDay();
        }

        return $count;
    }

    /**
     * Create attendance records for each weekday in a business trip period
     */
    public function createBusinessTripAttendances(BusinessTrip $trip): int
    {
        $user = $trip->user;
        $shift = $this->getApplicableShift($user);
        $startDate = $trip->start_date->copy();
        $endDate = $trip->end_date->copy();
        $count = 0;

        while ($startDate->lte($endDate)) {
            if ($startDate->isWeekday()) {
                $exists = Attendance::where('user_id', $user->id)
                    ->whereDate('check_in_time', $startDate)
                    ->exists();

                if (!$exists) {
                    Attendance::create([
                        'user_id' => $user->id,
                        'business_trip_id' => $trip->id,
                        'shift_id' => $shift?->id,
                        'attendance_type' => $user->isNormalAttendance() ? 'normal' : 'shift',
                        'photo_path' => 'dinas_luar',
                        'check_in_time' => $startDate->copy()->setTimeFromTimeString('00:00:00'),
                        'check_out_time' => $startDate->copy()->setTimeFromTimeString('00:00:00'),
                        'latitude' => 0,
                        'longitude' => 0,
                        'status' => 'dinas_luar',
                    ]);
                    $count++;
                }
            }
            $startDate->addDay();
        }

        return $count;
    }
}
