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
    ];

    /**
     * Get normal shift for OB
     */
    public static function getNormalShift()
    {
        return self::where('type', 'normal')->first();
    }

    /**
     * Get all shift-based entries for Satpam
     */
    public static function getSatpamShifts()
    {
        return self::where('type', 'shift')->get();
    }

    /**
     * Get current applicable shift based on time
     */
    public static function getCurrentShiftForTime($time)
    {
        $timeString = $time->format('H:i:s');
        
        return self::where('type', 'shift')
            ->whereRaw("TIME(?) BETWEEN start_time AND end_time", [$timeString])
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
