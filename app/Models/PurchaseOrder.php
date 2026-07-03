<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseOrder extends Model
{
    protected $table = 'purchase_order';
    protected $primaryKey = 'no_po';
    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'no_po', 'tanggal', 'nilai_po', 'id_supplier',
        'id_dept', 'status_po', 'dibuat_oleh',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'nilai_po' => 'decimal:2',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'id_supplier', 'id_supplier');
    }

    public function departemen(): BelongsTo
    {
        return $this->belongsTo(Departemen::class, 'id_dept', 'id_dept');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PoItem::class, 'no_po', 'no_po');
    }

    public function approvals(): HasMany
    {
        return $this->hasMany(Approval::class, 'no_po', 'no_po');
    }
}
