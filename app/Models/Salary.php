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
        'final_salary' => 'integer',
        'signed_at' => 'datetime',
    ];

    /**
     * Dapatkan potongan-potongan untuk gaji ini.
     */
    public function salaryDeductions()
    {
        return $this->hasMany(SalaryDeduction::class);
    }

    /**
     * Dapatkan pengguna pemilik gaji ini.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Dapatkan pengguna yang membuat data gaji ini.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Dapatkan pengguna yang menandatangani gaji ini.
     */
    public function signer()
    {
        return $this->belongsTo(User::class, 'signed_by');
    }

    /**
     * Periksa apakah gaji sudah ditandatangani.
     */
    public function isSigned(): bool
    {
        return $this->signed_by !== null && $this->signed_at !== null;
    }

    /**
     * Periksa apakah gaji berupa draf (belum ditandatangani).
     */
    public function isDraft(): bool
    {
        return !$this->isSigned();
    }

    /**
     * Dapatkan nama bulan yang diformat.
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
     * Dapatkan string periode (misal, "Januari 2026").
     */
    public function getPeriodAttribute(): string
    {
        return $this->month_name . ' ' . $this->year;
    }

    /**
     * Dapatkan label status.
     */
    public function getStatusLabelAttribute(): string
    {
        if ($this->isSigned()) {
            return 'Ditandatangani';
        }
        return 'Draft';
    }
}
