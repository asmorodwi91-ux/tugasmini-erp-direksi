<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Sanctum\HasApiTokens;

class AppUser extends Authenticatable
{
    use HasApiTokens;

    protected $table = 'app_user';
    protected $primaryKey = 'id_user';
    public $timestamps = false;

    protected $fillable = [
        'nama', 'email', 'password_hash', 'id_role',
        'status_2fa', 'failed_login', 'is_locked', 'is_active',
    ];

    protected $hidden = ['password_hash'];

    protected $casts = [
        'is_locked' => 'boolean',
        'is_active' => 'boolean',
        'failed_login' => 'integer',
    ];

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'id_role', 'id_role');
    }

    public function hakAkses(): HasMany
    {
        return $this->hasMany(HakAkses::class, 'id_user', 'id_user');
    }

    public function otpTokens(): HasMany
    {
        return $this->hasMany(OtpToken::class, 'id_user', 'id_user');
    }

    public function notifikasi(): HasMany
    {
        return $this->hasMany(Notifikasi::class, 'id_user', 'id_user');
    }
}
