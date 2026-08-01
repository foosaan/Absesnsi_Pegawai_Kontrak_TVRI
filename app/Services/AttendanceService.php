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
    const MAX_CHECKOUT_HOURS = 3; // Jam setelah shift berakhir untuk mengizinkan checkout

    /**
     * Get the applicable shift for a user based on their attendance type and current time
     */
    public function getApplicableShift(User $user, ?Carbon $time = null): ?Shift
    {
        $time = $time ?? Carbon::now();

        // Gunakan attendance_type untuk menentukan tipe shift
        if ($user->isUmumAttendance()) {
            return null; // Tipe umum tidak butuh shift
        }

        if ($user->isNormalAttendance()) {
            return Shift::getNormalShift();
        }

        if ($user->isShiftAttendance()) {
            return $this->getShiftForTime($time);
        }

        // Default ke shift normal
        return Shift::getNormalShift();
    }

    /**
     * Get shift based on current time — reads from DB dynamically
     * Prioritaskan shift yang akan datang dalam 1 jam ke depan (toleransi awal),
     * supaya check-in awal masuk ke shift yang benar, bukan shift sebelumnya.
     */
    public function getShiftForTime(Carbon $time): ?Shift
    {
        $timeString = $time->format('H:i:s');
        $oneHourLater = $time->copy()->addHour()->format('H:i:s');
        
        // 1. Cek apakah ada shift yang dimulai dalam 1 jam ke depan (toleransi awal)
        if ($oneHourLater > $timeString) {
            // Tidak lintas tengah malam (misal: 07:30 -> 08:30)
            $upcomingShift = Shift::where('type', 'shift')
                ->whereRaw('start_time > ? AND start_time <= ?', [$timeString, $oneHourLater])
                ->orderBy('start_time', 'asc')
                ->first();
        } else {
            // Lintas tengah malam (misal: 23:30 -> 00:30)
            $upcomingShift = Shift::where('type', 'shift')
                ->where(function ($q) use ($timeString, $oneHourLater) {
                    $q->whereRaw('start_time > ?', [$timeString])
                      ->orWhereRaw('start_time <= ?', [$oneHourLater]);
                })
                ->orderByRaw("CASE WHEN start_time > ? THEN 0 ELSE 1 END, start_time", [$timeString])
                ->first();
        }
        
        if ($upcomingShift) {
            return $upcomingShift;
        }
        
        // 2. Fallback: cari shift yang range-nya mencakup waktu sekarang
        // Range normal (start_time < end_time, misal 08:00-16:00)
        $shift = Shift::where('type', 'shift')
            ->whereRaw('start_time <= ? AND end_time > ?', [$timeString, $timeString])
            ->first();
        
        if ($shift) {
            return $shift;
        }
        
        // Range lintas tengah malam (start_time > end_time, misal 22:00-06:00)
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
            
            // Perbaikan pergantian hari (midnight crossing): jika waktu selesai sebelum check-in, berarti hari berikutnya
            if ($minCheckOut->lte($checkInTime)) {
                $minCheckOut->addDay();
            }
            
            return $minCheckOut;
        }
        
        // Jika tepat waktu, checkout = yang lebih akhir antara (check-in + 8 jam) dan (jam pulang shift)
        $durationBased = $checkInTime->copy()->addHours(self::WORK_DURATION_HOURS);
        
        // Hitung jam pulang shift
        $endTimeString = $shift->end_time;
        if ($endTimeString instanceof Carbon) {
            $endTimeString = $endTimeString->format('H:i:s');
        }
        $shiftEnd = $checkInTime->copy()->setTimeFromTimeString($endTimeString);
        if ($shiftEnd->lte($checkInTime)) {
            $shiftEnd->addDay();
        }
        
        // Ambil yang paling akhir — supaya absen awal tetap pulang minimal sesuai jam shift
        return $durationBased->gt($shiftEnd) ? $durationBased : $shiftEnd;
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
        
        // Perbaikan pergantian hari (midnight crossing): jika waktu selesai sebelum check-in, berarti hari berikutnya
        if ($maxCheckOut->lte($checkInTime)) {
            $maxCheckOut->addDay();
        }
        
        return $maxCheckOut->addHours(self::MAX_CHECKOUT_HOURS);
    }

    /**
     * Check if user is late based on their shift
     * FIX: Handle midnight crossing — jika check-in sebelum shift mulai (toleransi awal), tidak terlambat
     */
    public function isLate(Shift $shift, Carbon $checkInTime): bool
    {
        // Uraikan (parse) waktu mulai shift
        $startTimeString = $shift->start_time;
        if ($startTimeString instanceof Carbon) {
            $startTimeString = $startTimeString->format('H:i:s');
        }
        
        $shiftStart = Carbon::createFromFormat('H:i:s', $startTimeString);
        $toleranceEnd = $shiftStart->copy()->addMinutes($shift->tolerance_minutes);
        $checkInTimeOnly = Carbon::createFromFormat('H:i:s', $checkInTime->format('H:i:s'));
        
        $shiftStartHour = (int) $shiftStart->format('H');
        $checkInHour = (int) $checkInTimeOnly->format('H');
        
        // Tangani pergantian hari (midnight crossing) (misal shift 00:00, check-in 23:xx)
        // Jika shift mulai di jam kecil (00-03) dan check-in di jam besar (22-23)
        // → berarti check-in SEBELUM shift (early), pasti tidak terlambat
        if ($shiftStartHour < 4 && $checkInHour >= 22) {
            return false;
        }
        
        // Tangani pergantian hari (midnight crossing) untuk toleransi (misal shift 23:00, toleransi 30menit → 23:30)
        // Jika toleransi lintas hari (misal toleranceEnd = 00:15 dari shift 23:45)
        if ($toleranceEnd->format('H:i:s') < $shiftStart->format('H:i:s')) {
            // Toleransi melewati tengah malam
            // Terlambat jika: check-in > toleranceEnd DAN check-in < shiftStart
            return $checkInTimeOnly->gt($toleranceEnd) && $checkInTimeOnly->lt($shiftStart);
        }
        
        // Kasus normal: terlambat jika check-in > batas toleransi
        return $checkInTimeOnly->gt($toleranceEnd);
    }

    /**
     * Hitung tanggal kerja (work_date) berdasarkan waktu check-in dan shift
     * Jika check-in sebelum shift dimulai (toleransi awal), work_date = tanggal shift mulai
     */
    public function calculateWorkDate(Carbon $checkInTime, Shift $shift): Carbon
    {
        $startTimeString = $shift->start_time;
        if ($startTimeString instanceof Carbon) {
            $startTimeString = $startTimeString->format('H:i:s');
        }
        
        $checkInHour = (int) $checkInTime->format('H');
        $shiftStartHour = (int) Carbon::createFromFormat('H:i:s', $startTimeString)->format('H');
        
        // Jika shift mulai jam 00:xx dan check-in jam 23:xx → work_date = besok
        if ($shiftStartHour < 4 && $checkInHour >= 22) {
            return $checkInTime->copy()->addDay()->startOfDay();
        }
        
        // Default: work_date = tanggal check-in
        return $checkInTime->copy()->startOfDay();
    }

    /**
     * Check if user can check-in now
     * Returns ['can' => bool, 'message' => string, 'shift' => ?Shift]
     */
    public function canCheckIn(User $user): array
    {
        $now = Carbon::now();

        // Cek apakah ada attendance aktif (belum checkout) dalam 24 jam terakhir
        $activeAttendance = Attendance::where('user_id', $user->id)
            ->whereNull('check_out_time')
            ->where('check_in_time', '>=', $now->copy()->subHours(24))
            ->whereNotIn('status', ['cuti', 'dinas_luar', 'left'])
            ->first();

        if ($activeAttendance) {
            return [
                'can' => false,
                'message' => 'Anda masih memiliki absen aktif yang belum checkout.',
                'shift' => null,
            ];
        }

        // === TIPE UMUM: bebas check-in 24 jam, tidak butuh shift ===
        if ($user->isUmumAttendance()) {
            $workDate = $now->copy()->startOfDay();

            // Cek duplikat hari ini
            $existing = Attendance::where('user_id', $user->id)
                ->where('work_date', $workDate->toDateString())
                ->first();

            if ($existing) {
                $message = match($existing->status) {
                    'cuti'      => 'Anda sedang cuti hari ini.',
                    'dinas_luar'=> 'Anda sedang dinas luar hari ini.',
                    default     => 'Anda sudah absen masuk hari ini.',
                };
                return ['can' => false, 'message' => $message, 'shift' => null];
            }

            // Cek cuti
            $activeLeave = Leave::where('user_id', $user->id)
                ->where('status', 'approved')
                ->where('start_date', '<=', $workDate)
                ->where('end_date', '>=', $workDate)
                ->first();

            if ($activeLeave) {
                return [
                    'can' => false,
                    'message' => 'Anda sedang cuti (' . $activeLeave->type_label . ') sampai ' . $activeLeave->end_date->format('d M Y') . '.',
                    'shift' => null,
                ];
            }

            // Cek dinas luar
            $activeTrip = BusinessTrip::where('user_id', $user->id)
                ->where('status', 'approved')
                ->where('start_date', '<=', $workDate)
                ->where('end_date', '>=', $workDate)
                ->first();

            if ($activeTrip) {
                return [
                    'can' => false,
                    'message' => 'Anda sedang dinas luar ke ' . $activeTrip->destination . ' sampai ' . $activeTrip->end_date->format('d M Y') . '.',
                    'shift' => null,
                ];
            }

            return [
                'can'     => true,
                'message' => 'Silakan lakukan Absen Masuk.',
                'shift'   => null,
                'is_late' => false,
            ];
        }
        // === END TIPE UMUM ===

        // Tentukan shift yang berlaku saat ini
        $shift = $this->getApplicableShift($user, $now);

        if (!$shift) {
            return [
                'can' => false,
                'message' => 'Tidak ada shift yang berlaku untuk waktu ini.',
                'shift' => null,
            ];
        }

        // Hitung work_date untuk shift ini
        $workDate = $this->calculateWorkDate($now, $shift);

        // Cek duplikat berdasarkan work_date
        $existing = Attendance::where('user_id', $user->id)
            ->where('work_date', $workDate->toDateString())
            ->first();

        if ($existing) {
            $message = $existing->status === 'cuti'
                ? 'Anda sedang cuti hari ini.'
                : ($existing->status === 'dinas_luar'
                    ? 'Anda sedang dinas luar hari ini.'
                    : 'Anda sudah absen masuk untuk shift ini.');
            return [
                'can' => false,
                'message' => $message,
                'shift' => null,
            ];
        }

        // Periksa apakah pengguna memiliki cuti yang disetujui untuk tanggal kerja (work_date)
        $activeLeave = Leave::where('user_id', $user->id)
            ->where('status', 'approved')
            ->where('start_date', '<=', $workDate)
            ->where('end_date', '>=', $workDate)
            ->first();

        if ($activeLeave) {
            return [
                'can' => false,
                'message' => 'Anda sedang cuti (' . $activeLeave->type_label . ') sampai ' . $activeLeave->end_date->format('d M Y') . '.',
                'shift' => null,
            ];
        }

        // Periksa apakah pengguna memiliki dinas luar yang disetujui untuk tanggal kerja (work_date)
        $activeTrip = BusinessTrip::where('user_id', $user->id)
            ->where('status', 'approved')
            ->where('start_date', '<=', $workDate)
            ->where('end_date', '>=', $workDate)
            ->first();

        if ($activeTrip) {
            return [
                'can' => false,
                'message' => 'Anda sedang dinas luar ke ' . $activeTrip->destination . ' sampai ' . $activeTrip->end_date->format('d M Y') . '.',
                'shift' => null,
            ];
        }

        // Periksa apakah masih dalam jam kerja
        $startTimeString = $shift->start_time;
        if ($startTimeString instanceof Carbon) {
            $startTimeString = $startTimeString->format('H:i:s');
        }
        $startTime = Carbon::createFromFormat('H:i:s', $startTimeString);
        
        // Izinkan check-in hingga 1 jam sebelum shift dimulai
        $earlyTolerance = $startTime->copy()->subHour();
        $nowTimeOnly = Carbon::createFromFormat('H:i:s', $now->format('H:i:s'));
        
        // Tangani pergantian hari (midnight crossing) untuk toleransi (misal shift 00:00, toleransi 23:00)
        $shiftStartHour = (int) $startTime->format('H');
        $toleranceHour = (int) $earlyTolerance->format('H');
        
        // Jika toleransi lintas hari (misal toleransi 23:00 untuk shift 00:00)
        if ($toleranceHour > $shiftStartHour) {
            // Boleh check-in jika jam >= toleransi ATAU jam <= shift start + beberapa jam
            $allowed = $nowTimeOnly->gte($earlyTolerance) || $nowTimeOnly->lte($startTime->copy()->addHours(2));
        } else {
            $allowed = $nowTimeOnly->gte($earlyTolerance);
        }
        
        if (!$allowed) {
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
     * FIX: Cari attendance aktif dalam 24 jam terakhir, bukan hanya hari ini
     */
    public function canCheckOut(User $user): array
    {
        $now = Carbon::now();

        // Cari kehadiran (attendance) aktif (belum checkout) dalam 24 jam terakhir
        $attendance = Attendance::where('user_id', $user->id)
            ->whereNull('check_out_time')
            ->where('check_in_time', '>=', $now->copy()->subHours(24))
            ->whereNotIn('status', ['cuti', 'dinas_luar'])
            ->orderBy('check_in_time', 'desc')
            ->first();

        if (!$attendance) {
            return [
                'can' => false,
                'message' => 'Anda belum absen masuk.',
                'remaining_minutes' => 0,
                'attendance' => null,
            ];
        }

        // Periksa apakah status adalah 'left' (meninggalkan kantor)
        if ($attendance->status === 'left') {
            return [
                'can' => false,
                'message' => 'Batas waktu absen pulang telah terlewat. Status: Meninggalkan Kantor.',
                'remaining_minutes' => 0,
                'attendance' => $attendance,
            ];
        }

        // Periksa apakah waktu checkout maksimum telah terlewat
        if ($attendance->max_check_out_time && $now->gt($attendance->max_check_out_time)) {
            // Tandai otomatis sebagai 'left' (meninggalkan kantor)
            $attendance->update(['status' => 'left']);
            return [
                'can' => false,
                'message' => 'Batas waktu absen pulang telah terlewat. Status: Meninggalkan Kantor.',
                'remaining_minutes' => 0,
                'attendance' => $attendance,
            ];
        }

        // Periksa durasi kerja minimum
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

        // === TIPE UMUM: tidak butuh shift, bebas 24 jam ===
        if ($user->isUmumAttendance()) {
            $workDate = $now->copy()->startOfDay();
            $minCheckOutTime = $now->copy()->addHours(self::WORK_DURATION_HOURS);

            return DB::transaction(function () use ($user, $data, $now, $workDate, $minCheckOutTime) {
                $existing = Attendance::where('user_id', $user->id)
                    ->where('work_date', $workDate->toDateString())
                    ->lockForUpdate()
                    ->first();

                if ($existing) {
                    throw new \Exception('Anda sudah absen masuk hari ini.');
                }

                return Attendance::create([
                    'user_id'              => $user->id,
                    'shift_id'             => null,
                    'attendance_type'      => 'umum',
                    'photo_path'           => $data['photo_path'],
                    'check_in_time'        => $now,
                    'work_date'            => $workDate->toDateString(),
                    'min_check_out_time'   => $minCheckOutTime,
                    'max_check_out_time'   => null, // Tidak ada batas waktu checkout
                    'latitude'             => $data['latitude'],
                    'longitude'            => $data['longitude'],
                    'location_accuracy'    => $data['location_accuracy'] ?? null,
                    'is_mock_location'     => $data['is_mock_location'] ?? false,
                    'status'               => 'present', // Tidak pernah late
                ]);
            });
        }
        // === END TIPE UMUM ===

        $shift = $this->getApplicableShift($user, $now);
        $isLate = $this->isLate($shift, $now);
        $minCheckOutTime = $this->calculateMinCheckOutTime($now, $shift, $isLate);
        $maxCheckOutTime = $this->calculateMaxCheckOutTime($now, $shift);
        $workDate = $this->calculateWorkDate($now, $shift);

        // Cegah kondisi race (race condition): periksa kembali di dalam transaksi
        return DB::transaction(function () use ($user, $shift, $data, $now, $isLate, $minCheckOutTime, $maxCheckOutTime, $workDate) {
            // Periksa ulang: cek work_date DAN kehadiran (attendance) aktif
            $existing = Attendance::where('user_id', $user->id)
                ->where('work_date', $workDate->toDateString())
                ->lockForUpdate()
                ->first();
            
            if ($existing) {
                throw new \Exception('Anda sudah absen masuk untuk shift ini.');
            }

            return Attendance::create([
                'user_id'           => $user->id,
                'shift_id'          => $shift->id,
                'attendance_type'   => $user->isNormalAttendance() ? 'normal' : 'shift',
                'photo_path'        => $data['photo_path'],
                'check_in_time'     => $now,
                'work_date'         => $workDate->toDateString(),
                'min_check_out_time'=> $minCheckOutTime,
                'max_check_out_time'=> $maxCheckOutTime,
                'latitude'          => $data['latitude'],
                'longitude'         => $data['longitude'],
                'location_accuracy' => $data['location_accuracy'] ?? null,
                'is_mock_location'  => $data['is_mock_location'] ?? false,
                'status'            => $isLate ? 'late' : 'present',
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
            if ($startDate->isWeekday()) {
                $exists = Attendance::where('user_id', $user->id)
                    ->where('work_date', $startDate->toDateString())
                    ->exists();

                if (!$exists) {
                    Attendance::create([
                        'user_id' => $user->id,
                        'leave_id' => $leave->id,
                        'shift_id' => $shift?->id,
                        'attendance_type' => $user->isUmumAttendance() ? 'umum' : ($user->isNormalAttendance() ? 'normal' : 'shift'),
                        'photo_path' => 'cuti',
                        'check_in_time' => $startDate->copy()->setTimeFromTimeString('00:00:00'),
                        'check_out_time' => $startDate->copy()->setTimeFromTimeString('00:00:00'),
                        'work_date' => $startDate->toDateString(),
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
                    ->where('work_date', $startDate->toDateString())
                    ->exists();

                if (!$exists) {
                    Attendance::create([
                        'user_id' => $user->id,
                        'business_trip_id' => $trip->id,
                        'shift_id' => $shift?->id,
                        'attendance_type' => $user->isUmumAttendance() ? 'umum' : ($user->isNormalAttendance() ? 'normal' : 'shift'),
                        'photo_path' => 'dinas_luar',
                        'check_in_time' => $startDate->copy()->setTimeFromTimeString('00:00:00'),
                        'check_out_time' => $startDate->copy()->setTimeFromTimeString('00:00:00'),
                        'work_date' => $startDate->toDateString(),
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
