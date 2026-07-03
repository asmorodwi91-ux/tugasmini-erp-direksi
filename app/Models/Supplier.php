<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supplier extends Model
{
    protected $table = 'supplier';
    protected $primaryKey = 'id_supplier';
    public $timestamps = false;

    protected $fillable = ['nama_supplier', 'alamat', 'kontak', 'skor_supplier'];

    protected $casts = ['skor_supplier' => 'decimal:2'];

    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class, 'id_supplier', 'id_supplier');
    }
}
