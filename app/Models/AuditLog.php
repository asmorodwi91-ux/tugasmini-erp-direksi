<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    protected $table = 'audit_log';
    protected $primaryKey = 'id_log';
    public $timestamps = false;

    protected $fillable = [
        'id_user', 'aksi', 'modul',
        'data_sebelum', 'data_sesudah', 'perangkat_ip',
    ];

    protected $casts = [
        'data_sebelum' => 'array',
        'data_sesudah' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(AppUser::class, 'id_user', 'id_user');
    }

    // Penegakan immutability di level aplikasi (DB juga punya trigger)
    protected static function booted(): void
    {
        static::updating(fn () => throw new \RuntimeException('audit_log bersifat permanen: UPDATE tidak diizinkan.'));
        static::deleting(fn () => throw new \RuntimeException('audit_log bersifat permanen: DELETE tidak diizinkan.'));
    }
}
