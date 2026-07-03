<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AgingPiutang extends Model
{
    protected $table = 'aging_piutang';
    protected $primaryKey = 'id_aging';
    public $timestamps = false;

    protected $fillable = ['id_dept', 'periode', 'jenis', 'bucket_umur', 'jumlah'];

    protected $casts = ['jumlah' => 'decimal:2'];

    public function departemen(): BelongsTo
    {
        return $this->belongsTo(Departemen::class, 'id_dept', 'id_dept');
    }
}
