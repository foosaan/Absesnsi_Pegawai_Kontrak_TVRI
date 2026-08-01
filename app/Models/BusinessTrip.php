<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class BusinessTrip extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        static::deleting(function (BusinessTrip $trip) {
            if ($trip->attachment) {
                Storage::disk('public')->delete($trip->attachment);
            }
        });
    }

    protected $fillable = [
        'user_id',
        'start_date',
        'end_date',
        'destination',
        'purpose',
        'attachment',
        'status',
        'approved_by',
        'rejection_reason',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'pending' => 'Menunggu Persetujuan',
            'approved' => 'Disetujui',
            'rejected' => 'Ditolak',
            default => $this->status,
        };
    }

    public function getTotalDaysAttribute(): int
    {
        return (int) $this->start_date->diffInDays($this->end_date) + 1;
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }
}
