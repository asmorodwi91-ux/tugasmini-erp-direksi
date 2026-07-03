<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RincianUsulan extends Model
{
    protected $table = 'rincian_usulan';
    protected $primaryKey = 'id_rincian';
    public $timestamps = false;

    protected $fillable = ['id_usulan', 'pos_anggaran', 'realisasi_lalu', 'plafon_diajukan'];

    protected $casts = [
        'realisasi_lalu' => 'decimal:2',
        'plafon_diajukan' => 'decimal:2',
    ];

    public function usulan(): BelongsTo
    {
        return $this->belongsTo(UsulanAnggaran::class, 'id_usulan', 'id_usulan');
    }
}
