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
     * Dapatkan shift yang diubah
     */
    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }

    /**
     * Dapatkan pengguna yang melakukan perubahan
     */
    public function changedByUser()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }

    /**
     * Dapatkan nama field yang dapat dibaca manusia
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
