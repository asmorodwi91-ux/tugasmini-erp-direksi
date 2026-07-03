<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Approval extends Model
{
    protected $table = 'approval';
    protected $primaryKey = 'id_approval';
    public $timestamps = false;

    protected $fillable = [
        'no_po', 'id_user', 'keputusan', 'catatan',
        'perangkat_ip', 'link_token', 'status_link', 'waktu_putusan',
    ];

    protected $casts = ['waktu_putusan' => 'datetime'];

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class, 'no_po', 'no_po');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(AppUser::class, 'id_user', 'id_user');
    }
}
