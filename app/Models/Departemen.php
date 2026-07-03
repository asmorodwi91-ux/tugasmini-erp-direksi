<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Departemen extends Model
{
    protected $table = 'departemen';
    protected $primaryKey = 'id_dept';
    public $timestamps = false;

    protected $fillable = ['nama_dept', 'kepala_dept'];

    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class, 'id_dept', 'id_dept');
    }

    public function laporanKeuangan(): HasMany
    {
        return $this->hasMany(LaporanKeuangan::class, 'id_dept', 'id_dept');
    }

    public function kinerja(): HasMany
    {
        return $this->hasMany(KinerjaOperasional::class, 'id_dept', 'id_dept');
    }
}
