<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LaporanKeuangan extends Model
{
    protected $table = 'laporan_keuangan';
    protected $primaryKey = 'id_laporan';
    public $timestamps = false;

    // 'laba' adalah generated column (read-only), tidak boleh diisi manual
    protected $fillable = [
        'id_dept', 'periode', 'pemasukan', 'pengeluaran',
        'persen_anggaran', 'indikator_warna',
    ];

    protected $casts = [
        'pemasukan' => 'decimal:2',
        'pengeluaran' => 'decimal:2',
        'laba' => 'decimal:2',
        'persen_anggaran' => 'decimal:2',
    ];

    public function departemen(): BelongsTo
    {
        return $this->belongsTo(Departemen::class, 'id_dept', 'id_dept');
    }
}
