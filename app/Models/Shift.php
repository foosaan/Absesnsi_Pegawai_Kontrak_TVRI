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
     * Get normal shift
     */
    public static function getNormalShift()
    {
        return self::where('type', 'normal')->first();
    }

    /**
     * Get all shift-based schedules
     */
    public static function getShiftSchedules()
    {
        return self::where('type', 'shift')->get();
    }

    /**
     * Get current applicable shift based on time (handles midnight-crossing)
     */
    public static function getCurrentShiftForTime($time)
    {
        $timeString = $time->format('H:i:s');
        
        // Normal range: start_time < end_time (e.g. 08:00-16:00)
        $shift = self::where('type', 'shift')
            ->whereRaw('start_time <= ? AND end_time > ?', [$timeString, $timeString])
            ->first();
        
        if ($shift) {
            return $shift;
        }
        
        // Midnight-crossing: start_time > end_time (e.g. 22:00-06:00)
        return self::where('type', 'shift')
            ->whereRaw('start_time > end_time')
            ->where(function ($query) use ($timeString) {
                $query->whereRaw('? >= start_time', [$timeString])
                      ->orWhereRaw('? < end_time', [$timeString]);
            })
            ->first();
    }

    /**
     * Attendances using this shift
     */
    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }
}
