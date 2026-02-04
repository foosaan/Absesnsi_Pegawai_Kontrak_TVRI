<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Shift;

class Attendance extends Model
{
    protected $fillable = [
        'user_id',
        'shift_id',
        'attendance_type',
        'photo_path',
        'check_out_photo_path',
        'check_in_time',
        'check_out_time',
        'min_check_out_time',
        'latitude',
        'longitude',
        'check_out_latitude',
        'check_out_longitude',
        'status',
    ];

    protected $casts = [
        'check_in_time' => 'datetime',
        'check_out_time' => 'datetime',
        'min_check_out_time' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }

    /**
     * Check if minimum work duration has been met
     */
    public function canCheckOut(): bool
    {
        if (!$this->min_check_out_time) {
            return true;
        }
        return now()->gte($this->min_check_out_time);
    }

    /**
     * Get remaining time before allowed to check out
     */
    public function getRemainingMinutes(): int
    {
        if (!$this->min_check_out_time || $this->canCheckOut()) {
            return 0;
        }
        return now()->diffInMinutes($this->min_check_out_time, false);
    }
}
