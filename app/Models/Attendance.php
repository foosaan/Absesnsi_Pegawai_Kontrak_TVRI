<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use App\Models\Shift;

class Attendance extends Model
{
    protected static function booted(): void
    {
        static::deleting(function (Attendance $attendance) {
            if ($attendance->photo_path) {
                Storage::disk('public')->delete($attendance->photo_path);
            }
            if ($attendance->check_out_photo_path) {
                Storage::disk('public')->delete($attendance->check_out_photo_path);
            }
        });
    }
    protected $fillable = [
        'user_id',
        'shift_id',
        'leave_id',
        'business_trip_id',
        'attendance_type',
        'photo_path',
        'check_out_photo_path',
        'check_in_time',
        'check_out_time',
        'min_check_out_time',
        'max_check_out_time',
        'latitude',
        'longitude',
        'location_accuracy',
        'check_out_latitude',
        'check_out_longitude',
        'check_out_location_accuracy',
        'is_mock_location',
        'status',
        'manual_reason',
        'created_by',
        'work_date',
    ];

    protected $casts = [
        'check_in_time' => 'datetime',
        'check_out_time' => 'datetime',
        'min_check_out_time' => 'datetime',
        'max_check_out_time' => 'datetime',
        'is_mock_location' => 'boolean',
        'work_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }

    public function leave()
    {
        return $this->belongsTo(\App\Models\Leave::class);
    }

    public function businessTrip()
    {
        return $this->belongsTo(\App\Models\BusinessTrip::class);
    }

    public function createdByUser()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Periksa apakah durasi kerja minimum telah terpenuhi
     */
    public function canCheckOut(): bool
    {
        if (!$this->min_check_out_time) {
            return true;
        }
        return now()->gte($this->min_check_out_time);
    }

    /**
     * Dapatkan sisa waktu sebelum diizinkan untuk check-out
     */
    public function getRemainingMinutes(): int
    {
        if (!$this->min_check_out_time || $this->canCheckOut()) {
            return 0;
        }
        return (int) now()->diffInMinutes($this->min_check_out_time, false);
    }
}
