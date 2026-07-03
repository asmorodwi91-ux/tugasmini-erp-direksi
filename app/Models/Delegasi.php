<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Delegasi extends Model
{
    protected $table = 'delegasi';
    protected $primaryKey = 'id_delegasi';
    public $timestamps = false;

    protected $fillable = [
        'id_user_asal', 'id_user_ganti', 'jenis_transaksi',
        'tgl_mulai', 'tgl_selesai', 'is_aktif',
    ];

    protected $casts = [
        'tgl_mulai' => 'date',
        'tgl_selesai' => 'date',
        'is_aktif' => 'boolean',
    ];

    public function userAsal(): BelongsTo
    {
        return $this->belongsTo(AppUser::class, 'id_user_asal', 'id_user');
    }

    public function userGanti(): BelongsTo
    {
        return $this->belongsTo(AppUser::class, 'id_user_ganti', 'id_user');
    }
}
