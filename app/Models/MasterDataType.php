<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class MasterDataType extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug', 'scope', 'description', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            if (empty($model->slug)) {
                $model->slug = Str::slug($model->name, '_');
            }
        });
    }

    public function values()
    {
        return $this->hasMany(MasterDataValue::class)->orderBy('sort_order')->orderBy('value');
    }

    public function activeValues()
    {
        return $this->values()->where('is_active', true);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
