<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'employee_type',
        'attendance_type',
        'profile_photo',
        // Profile fields
        'nip',
        'npwp',
        'status_pegawai',
        'nomor_sk',
        'tanggal_sk',
        'status_pajak',
        'nomor_rekening',
        'nama_bank',
        'gaji_pokok',
        'alamat',
        'no_telepon',
        'tanggal_lahir',
        'jenis_kelamin',
    ];

    /**
     * Get profile photo URL
     */
    public function getProfilePhotoUrl(): string
    {
        if ($this->profile_photo) {
            return asset('storage/' . $this->profile_photo);
        }
        // Default avatar with initials
        $initials = collect(explode(' ', $this->name))->map(fn($n) => strtoupper($n[0] ?? ''))->take(2)->join('');
        return "https://ui-avatars.com/api/?name={$initials}&background=4F46E5&color=fff&size=128";
    }
    
    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function salaries()
    {
        return $this->hasMany(Salary::class);
    }

    /**
     * Check if user uses shift-based attendance
     */
    public function isShiftAttendance(): bool
    {
        return $this->attendance_type === 'shift';
    }

    /**
     * Check if user uses normal attendance
     */
    public function isNormalAttendance(): bool
    {
        return $this->attendance_type === 'normal' || $this->attendance_type === null;
    }

    /**
     * Check if user is OB (Office Boy) - legacy, not used for attendance anymore
     */
    public function isOB(): bool
    {
        return $this->employee_type === 'ob';
    }

    /**
     * Check if user is Satpam (Security)
     */
    public function isSatpam(): bool
    {
        return $this->employee_type === 'satpam';
    }

    /**
     * Check if user is Admin
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Get the display name for employee type
     */
    public function getEmployeeTypeLabel(): string
    {
        return match($this->employee_type) {
            'ob' => 'Office Boy',
            'satpam' => 'Satpam',
            default => 'Karyawan',
        };
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'tanggal_sk' => 'date',
            'tanggal_lahir' => 'date',
            'gaji_pokok' => 'decimal:2',
        ];
    }
}

