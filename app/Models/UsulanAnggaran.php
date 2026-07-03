<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UsulanAnggaran extends Model
{
    protected $table = 'usulan_anggaran';
    protected $primaryKey = 'id_usulan';
    public $timestamps = false;

    protected $fillable = [
        'id_dept', 'periode', 'plafon_diajukan', 'diajukan_oleh',
        'disetujui_oleh', 'status', 'catatan', 'waktu_putusan',
    ];

    protected $casts = [
        'plafon_diajukan' => 'decimal:2',
        'waktu_putusan' => 'datetime',
    ];

    public function departemen(): BelongsTo
    {
        return $this->belongsTo(Departemen::class, 'id_dept', 'id_dept');
    }

    public function pengaju(): BelongsTo
    {
        return $this->belongsTo(AppUser::class, 'diajukan_oleh', 'id_user');
    }

    public function penyetuju(): BelongsTo
    {
        return $this->belongsTo(AppUser::class, 'disetujui_oleh', 'id_user');
    }
}
