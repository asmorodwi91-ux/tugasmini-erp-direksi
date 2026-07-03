<?php

namespace App\Http\Controllers;

use App\Models\AppUser;
use App\Models\OtpToken;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    // POST /api/auth/login  — langkah 1: email + password, lalu kirim OTP
    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = AppUser::where('email', $data['email'])->first();

        // Akun terkunci (gagal 3x)
        if ($user && $user->is_locked) {
            return response()->json([
                'error' => 'account_locked',
                'message' => 'Akun terkunci. IT Admin telah diberi tahu.',
            ], 423);
        }

        // Cek kredensial
        if (! $user || ! Hash::check($data['password'], $user->password_hash)) {
            if ($user) {
                $user->failed_login += 1;
                if ($user->failed_login >= 3) {
                    $user->is_locked = true;
                    // notifikasi ke IT bisa di-dispatch di sini
                }
                $user->save();
            }
            return response()->json(['error' => 'invalid_credentials'], 401);
        }

        // Reset gagal login, buat OTP 6 angka berlaku 5 menit
        $user->failed_login = 0;
        $user->save();

        $kode = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        OtpToken::create([
            'id_user' => $user->id_user,
            'kode_otp' => $kode,
            'dibuat_at' => now(),
            'expired_at' => now()->addMinutes(5),
            'is_used' => false,
        ]);

        // Di produksi: kirim OTP via SMS. Untuk demo, kembalikan di response.
        return response()->json([
            'status' => 'otp_sent',
            'message' => 'Kode OTP dikirim ke HP, berlaku 5 menit.',
            'debug_otp' => $kode, // HAPUS di produksi
        ]);
    }

    // POST /api/auth/verify-otp — langkah 2: verifikasi OTP, kembalikan token
    public function verifyOtp(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email',
            'otp' => 'required|string|size:6',
        ]);

        $user = AppUser::where('email', $data['email'])->first();
        if (! $user) {
            return response()->json(['error' => 'invalid_or_expired_otp'], 401);
        }

        $otp = OtpToken::where('id_user', $user->id_user)
            ->where('kode_otp', $data['otp'])
            ->where('is_used', false)
            ->latest('id_otp')
            ->first();

        if (! $otp || ! $otp->isValid()) {
            return response()->json(['error' => 'invalid_or_expired_otp'], 401);
        }

        $otp->is_used = true;
        $otp->save();

        // Buat token Sanctum
        $token = $user->createToken('api-token')->plainTextToken;

        AuditLog::create([
            'id_user' => $user->id_user,
            'aksi' => 'login',
            'modul' => 'Auth',
            'perangkat_ip' => $request->ip(),
        ]);

        return response()->json([
            'token' => $token,
            'user' => [
                'id' => $user->id_user,
                'nama' => $user->nama,
                'role' => $user->role->nama_role ?? null,
            ],
        ]);
    }

    // POST /api/auth/logout
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['status' => 'logged_out']);
    }
}
