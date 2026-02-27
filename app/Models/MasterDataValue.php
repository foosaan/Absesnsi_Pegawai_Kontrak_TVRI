<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterDataValue extends Model
{
    use HasFactory;

    protected $fillable = ['master_data_type_id', 'value', 'description', 'is_active', 'sort_order'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function type()
    {
        return $this->belongsTo(MasterDataType::class, 'master_data_type_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
