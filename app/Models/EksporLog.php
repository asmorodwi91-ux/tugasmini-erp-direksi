<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EksporLog extends Model
{
    protected $table = 'ekspor_log';
    protected $primaryKey = 'id_ekspor';
    public $timestamps = false;

    protected $fillable = ['id_user', 'jenis_laporan', 'periode', 'format', 'waktu_unduh'];

    protected $casts = ['waktu_unduh' => 'datetime'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(AppUser::class, 'id_user', 'id_user');
    }
}
