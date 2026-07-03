<?php

namespace App\Http\Controllers;

use App\Models\UsulanAnggaran;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class BudgetController extends Controller
{
    // GET /api/budget/proposals — daftar usulan (menunggu didahulukan)
    public function index(Request $request)
    {
        $rows = UsulanAnggaran::with(['departemen', 'pengaju'])
            ->orderByRaw("FIELD(status,'menunggu','revisi','approved')")
            ->orderByDesc('created_at')
            ->get()
            ->map(fn ($u) => [
                'id' => $u->id_usulan,
                'departemen' => $u->departemen->nama_dept ?? null,
                'periode' => $u->periode,
                'plafon_diajukan' => $u->plafon_diajukan,
                'pengaju' => $u->pengaju->nama ?? null,
                'status' => $u->status,
            ]);

        return response()->json(['data' => $rows]);
    }

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

    // GET /api/budget/proposal/{id} — detail + pembanding usulan vs realisasi
    public function show(int $id)
    {
        $u = UsulanAnggaran::with(['departemen', 'pengaju', 'penyetuju', 'rincian'])->find($id);
        if (! $u) {
            return response()->json(['error' => 'usulan_not_found'], 404);
        }

        $rincian = $u->rincian->map(function ($r) {
            $realisasi = (float) $r->realisasi_lalu;
            $plafon = (float) $r->plafon_diajukan;
            $selisih = $realisasi > 0 ? round(($plafon - $realisasi) / $realisasi * 100, 1) : null;
            return [
                'pos' => $r->pos_anggaran,
                'realisasi_lalu' => $realisasi,
                'plafon_diajukan' => $plafon,
                'selisih_persen' => $selisih,
            ];
        })->values();

        $totalRealisasi = (float) $u->rincian->sum('realisasi_lalu');

        return response()->json([
            'id' => $u->id_usulan,
            'departemen' => $u->departemen->nama_dept ?? null,
            'periode' => $u->periode,
            'plafon_diajukan' => $u->plafon_diajukan,
            'realisasi_lalu' => $totalRealisasi,
            'pengaju' => $u->pengaju->nama ?? null,
            'penyetuju' => $u->penyetuju->nama ?? null,
            'tanggal_pengajuan' => optional($u->created_at)->format('Y-m-d'),
            'status' => $u->status,
            'catatan' => $u->catatan,
            'rincian' => $rincian,
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
