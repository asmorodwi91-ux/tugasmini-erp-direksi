<?php

namespace App\Http\Controllers;

use App\Models\AppUser;
use App\Models\HakAkses;
use App\Models\Modul;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AccessController extends Controller
{
    // GET /api/access/users — daftar pengguna + role
    public function users()
    {
        return response()->json([
            'data' => AppUser::with('role')->get()->map(fn ($u) => [
                'id' => $u->id_user,
                'nama' => $u->nama,
                'role' => $u->role->nama_role ?? null,
                'status' => $u->is_active ? 'Aktif' : 'Nonaktif',
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
