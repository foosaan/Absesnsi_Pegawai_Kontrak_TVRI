<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Leave extends Model
{
    use HasFactory;

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
     * Get the user this leave belongs to
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the user who approved/rejected this leave
     */
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Get type label
     */
    public function getTypeLabelAttribute(): string
    {
        return match($this->type) {
            'cuti_tahunan' => 'Cuti Tahunan',
            'sakit' => 'Sakit',
            'alasan_penting' => 'Alasan Penting',
            'lainnya' => 'Lainnya',
            default => $this->type,
        };
    }

    /**
     * Get status label
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
     * Get total days of leave
     */
    public function getTotalDaysAttribute(): int
    {
        return (int) $this->start_date->diffInDays($this->end_date) + 1;
    }

    /**
     * Scope pending leaves
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope approved leaves
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }
}
