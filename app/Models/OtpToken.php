<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OtpToken extends Model
{
    protected $table = 'otp_token';
    protected $primaryKey = 'id_otp';
    public $timestamps = false;

    protected $fillable = ['id_user', 'kode_otp', 'dibuat_at', 'expired_at', 'is_used'];

    protected $casts = [
        'dibuat_at' => 'datetime',
        'expired_at' => 'datetime',
        'is_used' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(AppUser::class, 'id_user', 'id_user');
    }

    // Helper: cek apakah OTP masih berlaku & belum dipakai
    public function isValid(): bool
    {
        return ! $this->is_used && now()->lessThanOrEqualTo($this->expired_at);
    }
}
