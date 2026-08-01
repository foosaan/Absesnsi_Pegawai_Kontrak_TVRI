<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    protected $fillable = [
        'user_id',
        'action',
        'model_type',
        'model_id',
        'description',
        'old_values',
        'new_values',
        'ip_address',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
    ];

    /**
     * Relasi ke pengguna (user)
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Dapatkan model terkait
     */
    public function subject()
    {
        return $this->morphTo('model');
    }

    /**
     * Catat log aktivitas
     */
    public static function log($action, $model, $description, $oldValues = null, $newValues = null)
    {
        return self::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'model_type' => get_class($model),
            'model_id' => $model->id ?? null,
            'description' => $description,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => request()->ip(),
        ]);
    }

    /**
     * Dapatkan warna aksi untuk tampilan
     */
    public function getActionColorAttribute()
    {
        return match($this->action) {
            'create' => 'green',
            'update' => 'blue',
            'delete' => 'red',
            default => 'gray',
        };
    }

    /**
     * Dapatkan ikon aksi untuk tampilan
     */
    public function getActionIconAttribute()
    {
        return match($this->action) {
            'create' => '➕',
            'update' => '✏️',
            'delete' => '🗑️',
            default => '📝',
        };
    }
}
