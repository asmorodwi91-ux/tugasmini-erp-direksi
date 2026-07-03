<?php

namespace App\Http\Controllers;

use App\Models\AppUser;
use App\Models\HakAkses;
use App\Models\Modul;
use App\Models\Role;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AccessController extends Controller
{
    // GET /api/access/roles — daftar role/jabatan (untuk dropdown)
    public function roles()
    {
        return response()->json([
            'data' => Role::orderBy('id_role')->get(['id_role', 'nama_role', 'level_akses', 'keterangan']),
        ]);
    }

    // POST /api/access/users — tambah pengguna baru
    public function store(Request $request)
    {
        $data = $request->validate([
            'nama' => 'required|string|max:100',
            'email' => 'required|email|max:150|unique:app_user,email',
            'id_role' => 'required|integer|exists:role,id_role',
            'password' => 'nullable|string|min:6',
        ]);

        $user = AppUser::create([
            'nama' => $data['nama'],
            'email' => $data['email'],
            'password_hash' => bcrypt($data['password'] ?? 'password'),
            'id_role' => $data['id_role'],
        ]);

        // Hak akses awal mengikuti level default role pada seluruh modul
        $role = Role::find($data['id_role']);
        $levelDefault = $role->level_akses ?? 'lihat';
        foreach (Modul::pluck('id_modul') as $idModul) {
            HakAkses::create([
                'id_user' => $user->id_user,
                'id_modul' => $idModul,
                'level' => $levelDefault,
            ]);
        }

        AuditLog::create([
            'id_user' => $request->user()->id_user,
            'aksi' => 'tambah_pengguna',
            'modul' => 'HakAkses',
            'data_sesudah' => ['id_user' => $user->id_user, 'nama' => $user->nama, 'id_role' => $data['id_role']],
            'perangkat_ip' => $request->ip(),
        ]);

        return response()->json([
            'id' => $user->id_user,
            'nama' => $user->nama,
            'role' => $role->nama_role ?? null,
            'status' => 'Aktif',
        ], 201);
    }
    // GET /api/access/users — daftar pengguna + role + hak akses per modul
    public function users()
    {
        // Peta hak akses semua user sekaligus: id_user => [nama_modul => level]
        $aksesAll = HakAkses::join('modul', 'hak_akses.id_modul', '=', 'modul.id_modul')
            ->get(['hak_akses.id_user', 'modul.nama_modul', 'hak_akses.level'])
            ->groupBy('id_user')
            ->map(fn ($rows) => $rows->pluck('level', 'nama_modul'));

        return response()->json([
            'data' => AppUser::with('role')->get()->map(fn ($u) => [
                'id' => $u->id_user,
                'nama' => $u->nama,
                'role' => $u->role->nama_role ?? null,
                'status' => $u->is_active ? 'Aktif' : 'Nonaktif',
                'akses' => $aksesAll[$u->id_user] ?? (object) [],
            ]),
        ]);
    }

    // POST /api/access/save — simpan hak akses (berlaku langsung + audit)
    public function save(Request $request)
    {
        $data = $request->validate([
            'user_id' => 'required|integer',
            'akses' => 'required|array',
            'akses.*.modul' => 'required|string',
            'akses.*.lihat' => 'boolean',
            'akses.*.ubah' => 'boolean',
            'akses.*.setujui' => 'boolean',
        ]);

        DB::transaction(function () use ($data, $request) {
            foreach ($data['akses'] as $a) {
                $modul = Modul::firstOrCreate(['nama_modul' => $a['modul']]);

                // Modul tanpa centang apa pun = akses dicabut (hapus barisnya)
                $diberi = ($a['lihat'] ?? false) || ($a['ubah'] ?? false) || ($a['setujui'] ?? false);
                if (! $diberi) {
                    HakAkses::where('id_user', $data['user_id'])
                        ->where('id_modul', $modul->id_modul)
                        ->delete();
                    continue;
                }

                $level = ($a['setujui'] ?? false) ? 'setujui'
                    : (($a['ubah'] ?? false) ? 'ubah' : 'lihat');

                HakAkses::updateOrCreate(
                    ['id_user' => $data['user_id'], 'id_modul' => $modul->id_modul],
                    ['level' => $level]
                );
            }

            AuditLog::create([
                'id_user' => $request->user()->id_user,
                'aksi' => 'ubah_hak_akses',
                'modul' => 'HakAkses',
                'data_sesudah' => $data,
                'perangkat_ip' => $request->ip(),
            ]);
        });

        return response()->json(['status' => 'saved', 'berlaku' => 'langsung']);
    }
}
