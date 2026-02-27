<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeaveBalance extends Model
{
    protected $fillable = [
        'user_id',
        'year',
        'initial_balance',
        'used',
        'remaining',
        'notes',
    ];

    /**
     * Relationship to user
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get or create leave balance for user and year
     */
    public static function getOrCreate($userId, $year, $initialBalance = 12)
    {
        return self::firstOrCreate(
            ['user_id' => $userId, 'year' => $year],
            [
                'initial_balance' => $initialBalance,
                'used' => 0,
                'remaining' => $initialBalance,
            ]
        );
    }

    /**
     * Use leave days
     */
    public function useLeave($days)
    {
        $this->used += $days;
        $this->remaining = $this->initial_balance - $this->used;
        $this->save();
        return $this;
    }

    /**
     * Add leave days (e.g., carry over)
     */
    public function addLeave($days)
    {
        $this->initial_balance += $days;
        $this->remaining = $this->initial_balance - $this->used;
        $this->save();
        return $this;
    }

    /**
     * Reset for new year
     */
    public static function initializeForNewYear($userId, $year, $carryOverDays = 0)
    {
        $baseBalance = 12;
        return self::create([
            'user_id' => $userId,
            'year' => $year,
            'initial_balance' => $baseBalance + $carryOverDays,
            'used' => 0,
            'remaining' => $baseBalance + $carryOverDays,
        ]);
    }
}
