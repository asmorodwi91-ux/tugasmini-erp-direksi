<?php

namespace App\Http\Controllers;

use App\Models\UsulanAnggaran;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class BudgetController extends Controller
{
    // POST /api/budget/proposal — ajukan usulan anggaran
    public function store(Request $request)
    {
        $data = $request->validate([
            'id_dept' => 'required|integer',
            'periode' => 'required|string',
            'plafon' => 'required|numeric|min:0',
        ]);

        $usulan = UsulanAnggaran::create([
            'id_dept' => $data['id_dept'],
            'periode' => $data['periode'],
            'plafon_diajukan' => $data['plafon'],
            'diajukan_oleh' => $request->user()->id_user,
            'status' => 'menunggu',
        ]);

        return response()->json(['id' => $usulan->id_usulan, 'status' => 'menunggu'], 201);
    }

    // GET /api/budget/proposal/{id} — detail + pembanding
    public function show(int $id)
    {
        $u = UsulanAnggaran::with('departemen')->find($id);
        if (! $u) {
            return response()->json(['error' => 'usulan_not_found'], 404);
        }

        return response()->json([
            'id' => $u->id_usulan,
            'departemen' => $u->departemen->nama_dept ?? null,
            'periode' => $u->periode,
            'plafon_diajukan' => $u->plafon_diajukan,
            'status' => $u->status,
        ]);
    }

    // POST /api/budget/decision — setujui / minta revisi
    public function decision(Request $request)
    {
        $data = $request->validate([
            'id' => 'required|integer',
            'keputusan' => 'required|in:setujui,revisi',
            'catatan' => 'nullable|string|max:255',
        ]);

        $u = UsulanAnggaran::find($data['id']);
        if (! $u) {
            return response()->json(['error' => 'usulan_not_found'], 404);
        }

        $u->status = $data['keputusan'] === 'setujui' ? 'approved' : 'revisi';
        $u->disetujui_oleh = $request->user()->id_user;
        $u->catatan = $data['catatan'] ?? null;
        $u->waktu_putusan = now();
        $u->save();

        AuditLog::create([
            'id_user' => $request->user()->id_user,
            'aksi' => 'keputusan_anggaran_' . $data['keputusan'],
            'modul' => 'Budget',
            'data_sesudah' => ['id_usulan' => $u->id_usulan, 'status' => $u->status],
            'perangkat_ip' => $request->ip(),
        ]);

        return response()->json(['id' => $u->id_usulan, 'status' => $u->status]);
    }
}
