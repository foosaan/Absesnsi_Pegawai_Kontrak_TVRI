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
        'nip',            // NIP tetap di users — dipakai semua role
        'profile_photo',
        'signature',
        'two_factor_enabled',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    /**
     * Dapatkan URL foto profil
     */
    public function getProfilePhotoUrl(): string
    {
        if ($this->profile_photo) {
            return asset('storage/' . $this->profile_photo);
        }
        // Avatar default dengan inisial
        $initials = collect(explode(' ', $this->name))->map(fn($n) => strtoupper($n[0] ?? ''))->take(2)->join('');
        return "https://ui-avatars.com/api/?name={$initials}&background=4F46E5&color=fff&size=128";
    }

    // ===================================================================
    // Relasi Utama
    // ===================================================================

    /**
     * Relasi One-to-One ke EmployeeProfile (hanya untuk role 'user')
     */
    public function profile()
    {
        return $this->hasOne(EmployeeProfile::class);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function salaries()
    {
        return $this->hasMany(Salary::class);
    }

    // ===================================================================
    // Accessor Delegasi — Backward Compatibility
    // Memungkinkan pemanggilan $user->nik, $user->jabatan, dll.
    // meskipun datanya sudah berpindah ke tabel employee_profiles.
    // NIP TIDAK perlu accessor karena tetap di tabel users.
    // ===================================================================

    public function getNikAttribute()
    {
        return $this->profile?->nik;
    }


    public function getAlamatAttribute()
    {
        return $this->profile?->alamat;
    }

    public function getNoTeleponAttribute()
    {
        return $this->profile?->no_telepon;
    }

    public function getTanggalLahirAttribute()
    {
        return $this->profile?->tanggal_lahir;
    }

    public function getJenisKelaminAttribute()
    {
        return $this->profile?->jenis_kelamin;
    }

    public function getAttendanceTypeAttribute()
    {
        return $this->profile?->attendance_type;
    }

    public function getJabatanIdAttribute()
    {
        return $this->profile?->jabatan_id;
    }

    public function getBagianIdAttribute()
    {
        return $this->profile?->bagian_id;
    }

    public function getStatusPegawaiIdAttribute()
    {
        return $this->profile?->status_pegawai_id;
    }

    public function getStatusOperasionalIdAttribute()
    {
        return $this->profile?->status_operasional_id;
    }

    /**
     * Accessors untuk string value master data (Backward Compatibility)
     */
    public function getJabatanAttribute()
    {
        return $this->profile?->jabatan;
    }

    public function getBagianAttribute()
    {
        return $this->profile?->bagian;
    }

    public function getStatusPegawaiAttribute()
    {
        return $this->profile?->status_pegawai;
    }

    public function getStatusOperasionalAttribute()
    {
        return $this->profile?->status_operasional;
    }

    /**
     * Relasi ke MasterData via profile (delegasi)
     */
    public function jabatanValue()
    {
        return $this->profile ? $this->profile->jabatanValue() : MasterData::query()->whereRaw('1=0');
    }

    public function bagianValue()
    {
        return $this->profile ? $this->profile->bagianValue() : MasterData::query()->whereRaw('1=0');
    }

    public function statusPegawaiValue()
    {
        return $this->profile ? $this->profile->statusPegawaiValue() : MasterData::query()->whereRaw('1=0');
    }

    public function statusOperasionalValue()
    {
        return $this->profile ? $this->profile->statusOperasionalValue() : MasterData::query()->whereRaw('1=0');
    }

    // ===================================================================
    // Helper Methods: Tipe Absensi
    // ===================================================================

    /**
     * Periksa apakah pengguna menggunakan absensi berbasis shift
     */
    public function isShiftAttendance(): bool
    {
        return $this->attendance_type === 'shift';
    }

    /**
     * Periksa apakah pengguna menggunakan absensi normal
     */
    public function isNormalAttendance(): bool
    {
        return $this->attendance_type === 'normal' || $this->attendance_type === null;
    }

    /**
     * Periksa apakah pengguna menggunakan absensi umum (bebas 24 jam)
     * Absen bebas 24 jam, tidak ada batasan waktu masuk, tidak ada status terlambat
     */
    public function isUmumAttendance(): bool
    {
        return $this->attendance_type === 'umum';
    }

    // ===================================================================
    // Helper Methods: Role & Jabatan
    // ===================================================================

    /**
     * Periksa apakah pengguna adalah Admin
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Periksa apakah pengguna adalah Satpam
     */
    public function isSatpam(): bool
    {
        return $this->jabatan === 'Satpam';
    }

    /**
     * Periksa apakah pengguna adalah OB (Office Boy)
     */
    public function isOB(): bool
    {
        return $this->jabatan === 'OB' || $this->jabatan === 'Office Boy';
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
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
            'two_factor_enabled' => 'boolean',
        ];
    }

    /**
     * Periksa apakah pengguna telah mengaktifkan 2FA
     */
    public function hasTwoFactorEnabled(): bool
    {
        return $this->two_factor_enabled && !empty($this->two_factor_secret);
    }

    /**
     * Periksa apakah role pengguna memerlukan 2FA
     */
    public function requiresTwoFactor(): bool
    {
        return $this->role === 'admin';
    }
}
