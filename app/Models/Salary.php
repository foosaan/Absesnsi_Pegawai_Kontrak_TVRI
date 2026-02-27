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
        'total_potongan_intern',
        'deductions',
        'final_salary',
        'created_by',
        'status',
        'signed_by',
        'signed_at',
        'notes',
    ];

    protected $casts = [
        'base_salary' => 'integer',
        'potongan_kppn' => 'integer',
        'total_potongan_intern' => 'integer',
        'deductions' => 'integer',
        'final_salary' => 'integer',
        'signed_at' => 'datetime',
    ];

    /**
     * Get the deductions for the salary.
     */
    public function salaryDeductions()
    {
        return $this->hasMany(SalaryDeduction::class);
    }

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
     * Get the user who signed this salary
     */
    public function signer()
    {
        return $this->belongsTo(User::class, 'signed_by');
    }

    /**
     * Check if salary is signed
     */
    public function isSigned(): bool
    {
        return $this->signed_by !== null && $this->signed_at !== null;
    }

    /**
     * Check if salary is draft (not signed)
     */
    public function isDraft(): bool
    {
        return !$this->isSigned();
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
        if ($this->isSigned()) {
            return 'Ditandatangani';
        }
        return 'Draft';
    }
}
