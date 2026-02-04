<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShiftLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'shift_id',
        'changed_by',
        'field_name',
        'old_value',
        'new_value',
    ];

    /**
     * Get the shift that was changed
     */
    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }

    /**
     * Get the user who made the change
     */
    public function changedByUser()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }

    /**
     * Get human-readable field name
     */
    public function getFieldLabelAttribute(): string
    {
        return match($this->field_name) {
            'start_time' => 'Jam Mulai',
            'end_time' => 'Jam Selesai',
            'tolerance_minutes' => 'Toleransi (menit)',
            default => $this->field_name,
        };
    }
}
