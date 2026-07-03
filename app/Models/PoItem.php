<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PoItem extends Model
{
    protected $table = 'po_item';
    // Composite primary key (no_po + nama_item) — tidak auto-increment
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = ['no_po', 'nama_item', 'qty', 'harga_satuan'];

    protected $casts = [
        'qty' => 'integer',
        'harga_satuan' => 'decimal:2',
    ];

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class, 'no_po', 'no_po');
    }
}
