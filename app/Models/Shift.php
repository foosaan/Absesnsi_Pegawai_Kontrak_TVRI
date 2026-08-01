<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Shift extends Model
{
    protected $fillable = [
        'name',
        'type',
        'start_time',
        'end_time',
        'tolerance_minutes',
    ];

    protected $casts = [
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
        'tolerance_minutes' => 'integer',
    ];

    /**
     * Dapatkan shift normal
     */
    public static function getNormalShift()
    {
        return self::where('type', 'normal')->first();
    }

    /**
     * Dapatkan semua jadwal berbasis shift
     */
    public static function getShiftSchedules()
    {
        return self::where('type', 'shift')->get();
    }

    /**
     * Dapatkan shift yang berlaku saat ini berdasarkan waktu (menangani pergantian hari/tengah malam)
     */
    public static function getCurrentShiftForTime($time)
    {
        $timeString = $time->format('H:i:s');
        
        // Rentang normal: start_time < end_time (misal 08:00-16:00)
        $shift = self::where('type', 'shift')
            ->whereRaw('start_time <= ? AND end_time > ?', [$timeString, $timeString])
            ->first();
        
        if ($shift) {
            return $shift;
        }
        
        // Pergantian hari/tengah malam: start_time > end_time (misal 22:00-06:00)
        return self::where('type', 'shift')
            ->whereRaw('start_time > end_time')
            ->where(function ($query) use ($timeString) {
                $query->whereRaw('? >= start_time', [$timeString])
                      ->orWhereRaw('? < end_time', [$timeString]);
            })
            ->first();
    }

    /**
     * Presensi yang menggunakan shift ini
     */
    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }
}
