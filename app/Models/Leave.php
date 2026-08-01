<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class Leave extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        static::deleting(function (Leave $leave) {
            if ($leave->attachment) {
                Storage::disk('public')->delete($leave->attachment);
            }
        });
    }

    protected $fillable = [
        'user_id',
        'start_date',
        'end_date',
        'reason',
        'attachment',
        'type',
        'status',
        'approved_by',
        'rejection_reason',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    /**
     * Dapatkan pengguna pemilik cuti ini
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Dapatkan pengguna yang menyetujui/menolak cuti ini
     */
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Dapatkan label tipe cuti
     */
    public function getTypeLabelAttribute(): string
    {
        return match($this->type) {
            'cuti_tahunan' => 'Cuti Tahunan',
            'sakit' => 'Sakit',
            'izin' => 'Izin',
            'lainnya' => 'Lainnya',
            default => $this->type,
        };
    }

    /**
     * Dapatkan label status
     */
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'pending' => 'Menunggu Persetujuan',
            'approved' => 'Disetujui',
            'rejected' => 'Ditolak',
            default => $this->status,
        };
    }

    /**
     * Dapatkan total hari cuti
     */
    public function getTotalDaysAttribute(): int
    {
        return (int) $this->start_date->diffInDays($this->end_date) + 1;
    }

    /**
     * Scope untuk cuti yang berstatus pending
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope untuk cuti yang disetujui
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }
}
