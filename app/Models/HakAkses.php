<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HakAkses extends Model
{
    protected $table = 'hak_akses';
    protected $primaryKey = 'id_akses';
    public $timestamps = false;

    protected $fillable = ['id_user', 'id_modul', 'level'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(AppUser::class, 'id_user', 'id_user');
    }

    public function modul(): BelongsTo
    {
        return $this->belongsTo(Modul::class, 'id_modul', 'id_modul');
    }
}
