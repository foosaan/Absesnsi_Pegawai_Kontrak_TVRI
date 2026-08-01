<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeProfile extends Model
{
    protected $fillable = [
        'user_id',
        'nik',
        'alamat',
        'no_telepon',
        'tanggal_lahir',
        'jenis_kelamin',
        'jabatan_id',
        'bagian_id',
        'status_pegawai_id',
        'status_operasional_id',
        'attendance_type',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_lahir' => 'date',
            'jabatan_id' => 'integer',
            'bagian_id' => 'integer',
            'status_pegawai_id' => 'integer',
            'status_operasional_id' => 'integer',
        ];
    }

    /**
     * Relasi ke User (One-to-One inverse)
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relasi ke MasterData
     */
    public function jabatanValue()
    {
        return $this->belongsTo(MasterData::class, 'jabatan_id');
    }

    public function bagianValue()
    {
        return $this->belongsTo(MasterData::class, 'bagian_id');
    }

    public function statusPegawaiValue()
    {
        return $this->belongsTo(MasterData::class, 'status_pegawai_id');
    }

    public function statusOperasionalValue()
    {
        return $this->belongsTo(MasterData::class, 'status_operasional_id');
    }

    /**
     * Accessor untuk string value jabatan
     */
    public function getJabatanAttribute()
    {
        return $this->jabatanValue?->value;
    }

    public function getBagianAttribute()
    {
        return $this->bagianValue?->value;
    }

    public function getStatusPegawaiAttribute()
    {
        return $this->statusPegawaiValue?->value;
    }

    public function getStatusOperasionalAttribute()
    {
        return $this->statusOperasionalValue?->value;
    }
}
