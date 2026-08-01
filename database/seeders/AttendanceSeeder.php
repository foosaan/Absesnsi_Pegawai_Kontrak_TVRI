<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Shift;
use App\Models\Attendance;
use Carbon\Carbon;

class AttendanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Ambil semua pegawai riil (karyawan kontrak) yang terdaftar di sistem
        $users = User::where('role', 'user')->get();

        if ($users->isEmpty()) {
            $this->command->warn('Tidak ada pegawai (role: user) yang ditemukan di database. Silakan daftarkan pegawai terlebih dahulu.');
            return;
        }

        // 2. Ambil referensi shift dari database
        $normalShift = Shift::getNormalShift();
        $shiftSchedules = Shift::getShiftSchedules();

        if (!$normalShift) {
            $this->command->error('Shift bertipe "normal" tidak ditemukan di database. Pastikan database sudah dimigrasi.');
            return;
        }

        // Lokasi TVRI Senayan sebagai pusat simulasi GPS
        $centerLat = -6.21412;
        $centerLng = 106.80583;

        // Rentang waktu: 30 hari ke belakang dari hari kemarin
        $startDate = Carbon::now()->subDays(30);
        $endDate = Carbon::now()->subDay(); // sampai kemarin agar tidak menimpa absen hari ini

        $createdCount = 0;

        $this->command->info("Memulai pengisian data dummy presensi untuk " . $users->count() . " pegawai...");

        foreach ($users as $user) {
            // Tentukan tipe presensi pegawai (default ke normal)
            $type = $user->attendance_type ?? 'normal';

            // Looping setiap hari dalam rentang 30 hari terakhir
            for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
                $workDateStr = $date->format('Y-m-d');

                // A. Cek apakah pegawai sudah memiliki absen di tanggal tersebut (agar tidak menimpa data asli)
                $exists = Attendance::where('user_id', $user->id)
                    ->where('work_date', $workDateStr)
                    ->exists();

                if ($exists) {
                    continue; // Skip jika sudah ada data absen asli
                }

                // B. Untuk tipe 'normal' dan 'umum', libur hari Sabtu & Minggu (skip)
                if (in_array($type, ['normal', 'umum']) && ($date->isSaturday() || $date->isSunday())) {
                    continue; 
                }

                // C. Untuk tipe 'shift', berikan hari libur acak (misal 1 hari dalam seminggu)
                if ($type === 'shift' && rand(1, 7) === 7) {
                    continue; 
                }

                // D. Inisialisasi variabel untuk record absen
                $shiftId = null;
                $checkInTime = null;
                $checkOutTime = null;
                $minCheckOutTime = null;
                $maxCheckOutTime = null;
                $status = 'present';

                // E. Bangun jam presensi berdasarkan tipe presensi
                if ($type === 'normal') {
                    $shiftId = $normalShift->id;
                    
                    // Jam masuk normal: 07:00. Toleransi 30 menit.
                    // 90% kesempatan tepat waktu (06:30 - 07:29). 10% terlambat (07:31 - 08:15).
                    if (rand(1, 10) <= 9) {
                        // Tepat waktu
                        $checkInMinutes = rand(-30, 29); // menit relatif terhadap 07:00
                        $checkInTime = $date->copy()->setTime(7, 0, 0)->addMinutes($checkInMinutes);
                        $status = 'present';
                    } else {
                        // Terlambat
                        $checkInMinutes = rand(31, 75); // lewat dari toleransi 07:30
                        $checkInTime = $date->copy()->setTime(7, 0, 0)->addMinutes($checkInMinutes);
                        $status = 'late';
                    }

                    // Jam pulang normal: 15:00.
                    $checkOutTime = $date->copy()->setTime(15, 0, 0)->addMinutes(rand(5, 90));
                    $minCheckOutTime = $date->copy()->setTime(15, 0, 0);
                    $maxCheckOutTime = $date->copy()->setTime(23, 59, 59);

                } elseif ($type === 'shift') {
                    // Pilih shift acak dari tipe shift yang ada
                    if ($shiftSchedules->isNotEmpty()) {
                        $selectedShift = $shiftSchedules->random();
                    } else {
                        $selectedShift = $normalShift; // Fallback jika tidak ada shift bertipe shift
                    }

                    $shiftId = $selectedShift->id;
                    $shiftStart = Carbon::parse($selectedShift->start_time);
                    $shiftEnd = Carbon::parse($selectedShift->end_time);

                    // Buat jam masuk berdasarkan start_time shift
                    if (rand(1, 10) <= 9) {
                        // Tepat waktu (30 menit sebelum s.d. batas toleransi)
                        $checkInMinutes = rand(-30, $selectedShift->tolerance_minutes - 1);
                        $checkInTime = $date->copy()->setTime($shiftStart->hour, $shiftStart->minute, 0)->addMinutes($checkInMinutes);
                        $status = 'present';
                    } else {
                        // Terlambat (lewat batas toleransi s.d. 60 menit berikutnya)
                        $checkInMinutes = rand($selectedShift->tolerance_minutes + 1, $selectedShift->tolerance_minutes + 60);
                        $checkInTime = $date->copy()->setTime($shiftStart->hour, $shiftStart->minute, 0)->addMinutes($checkInMinutes);
                        $status = 'late';
                    }

                    // Buat jam pulang berdasarkan end_time shift (apakah shift menyeberang hari atau tidak)
                    if ($shiftEnd->lt($shiftStart)) {
                        // Lintas hari (misal shift malam dari 22:00 - 06:00)
                        $checkOutTime = $date->copy()->addDay()->setTime($shiftEnd->hour, $shiftEnd->minute, 0)->addMinutes(rand(5, 60));
                        $minCheckOutTime = $date->copy()->addDay()->setTime($shiftEnd->hour, $shiftEnd->minute, 0);
                        $maxCheckOutTime = $date->copy()->addDay()->setTime($shiftEnd->hour, $shiftEnd->minute, 0)->addHours(4);
                    } else {
                        // Di hari yang sama
                        $checkOutTime = $date->copy()->setTime($shiftEnd->hour, $shiftEnd->minute, 0)->addMinutes(rand(5, 60));
                        $minCheckOutTime = $date->copy()->setTime($shiftEnd->hour, $shiftEnd->minute, 0);
                        $maxCheckOutTime = $date->copy()->setTime(23, 59, 59);
                    }

                } else {
                    // Tipe Umum (Fleksibel): bebas check-in jam 07:30 - 09:30 WIB
                    $checkInTime = $date->copy()->setTime(7, 30, 0)->addMinutes(rand(0, 120));
                    
                    // Durasi kerja minimum 8 jam + lembur acak 0-90 menit
                    $workDurationMinutes = 480 + rand(0, 90);
                    $checkOutTime = $checkInTime->copy()->addMinutes($workDurationMinutes);
                    
                    $minCheckOutTime = $checkInTime->copy()->addHours(8); // minimal 8 jam kerja
                    $maxCheckOutTime = null;
                    $status = 'present';
                }

                // F. Buat koordinat GPS dummy di sekitar kantor TVRI (presisi acak 10m - 100m)
                $latOffset = rand(-100, 100) / 1000000;
                $lngOffset = rand(-100, 100) / 1000000;
                $lat = $centerLat + $latOffset;
                $lng = $centerLng + $lngOffset;

                $latOutOffset = rand(-100, 100) / 1000000;
                $lngOutOffset = rand(-100, 100) / 1000000;
                $checkOutLat = $centerLat + $latOutOffset;
                $checkOutLng = $centerLng + $lngOutOffset;

                // G. Simpan data dummy ke database
                Attendance::create([
                    'user_id' => $user->id,
                    'shift_id' => $shiftId,
                    'attendance_type' => $type,
                    'check_in_time' => $checkInTime,
                    'check_out_time' => $checkOutTime,
                    'min_check_out_time' => $minCheckOutTime,
                    'max_check_out_time' => $maxCheckOutTime,
                    'latitude' => $lat,
                    'longitude' => $lng,
                    'location_accuracy' => rand(50, 150) / 10,
                    'check_out_latitude' => $checkOutLat,
                    'check_out_longitude' => $checkOutLng,
                    'check_out_location_accuracy' => rand(50, 150) / 10,
                    'is_mock_location' => false,
                    'status' => $status,
                    'work_date' => $workDateStr,
                    'photo_path' => 'dummy_photo.jpg',
                    'check_out_photo_path' => 'dummy_photo.jpg',
                ]);

                $createdCount++;
            }
        }

        $this->command->info("Berhasil membuat {$createdCount} data dummy presensi untuk pegawai TVRI!");
    }
}
