<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Salary extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'month',
        'year',
        'base_salary',
        'potongan_kppn',
        'simpanan_wajib',
        'kredit_uang',
        'kredit_toko',
        'dharma_wanita',
        'bpjs',
        'total_potongan_intern',
        'deductions',
        'final_salary',
        'created_by',
        'status',
        'notes',
    ];

    protected $casts = [
        'base_salary' => 'decimal:2',
        'potongan_kppn' => 'decimal:2',
        'simpanan_wajib' => 'decimal:2',
        'kredit_uang' => 'decimal:2',
        'kredit_toko' => 'decimal:2',
        'dharma_wanita' => 'decimal:2',
        'bpjs' => 'decimal:2',
        'total_potongan_intern' => 'decimal:2',
        'deductions' => 'decimal:2',
        'final_salary' => 'decimal:2',
    ];

    /**
     * Get the user this salary belongs to
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the user who created this salary record
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get formatted month name
     */
    public function getMonthNameAttribute(): string
    {
        $months = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];
        return $months[$this->month] ?? '';
    }

    /**
     * Get period string (e.g., "Januari 2026")
     */
    public function getPeriodAttribute(): string
    {
        return $this->month_name . ' ' . $this->year;
    }

    /**
     * Get status label
     */
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'draft' => 'Draft',
            'approved' => 'Disetujui',
            'paid' => 'Sudah Dibayar',
            default => $this->status,
        };
    }
}
