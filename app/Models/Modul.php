<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Modul extends Model
{
    protected $table = 'modul';
    protected $primaryKey = 'id_modul';
    public $timestamps = false;

    protected $fillable = ['nama_modul', 'deskripsi'];

    public function hakAkses(): HasMany
    {
        return $this->hasMany(HakAkses::class, 'id_modul', 'id_modul');
    }
}
