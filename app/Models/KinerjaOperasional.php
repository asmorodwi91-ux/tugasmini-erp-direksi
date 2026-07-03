<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KinerjaOperasional extends Model
{
    protected $table = 'kinerja_operasional';
    protected $primaryKey = 'id_kinerja';
    public $timestamps = false;

    protected $fillable = [
        'id_dept', 'periode', 'kategori',
        'nilai_aktual', 'target', 'skor',
    ];

    protected $casts = [
        'nilai_aktual' => 'decimal:2',
        'target' => 'decimal:2',
        'skor' => 'decimal:2',
    ];

    public function departemen(): BelongsTo
    {
        return $this->belongsTo(Departemen::class, 'id_dept', 'id_dept');
    }
}
