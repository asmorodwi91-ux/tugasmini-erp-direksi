<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notifikasi extends Model
{
    protected $table = 'notifikasi';
    protected $primaryKey = 'id_notif';
    public $timestamps = false;

    protected $fillable = [
        'id_user', 'jenis', 'pesan', 'level_kritis',
        'sumber_modul', 'dibaca',
    ];

    protected $casts = ['dibaca' => 'boolean'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(AppUser::class, 'id_user', 'id_user');
    }
}
