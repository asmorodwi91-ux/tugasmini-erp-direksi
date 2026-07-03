<?php

namespace App\Http\Controllers;

use App\Models\EksporLog;
use App\Models\AuditLog;
use App\Models\HakAkses;
use Illuminate\Http\Request;

class ExportController extends Controller
{
    // POST /api/export
    public function create(Request $request)
    {
        $data = $request->validate([
            'jenis' => 'required|string',
            'periode' => 'nullable|string',
            'format' => 'required|in:pdf,excel',
        ]);

        $user = $request->user();

        // Cek izin (contoh sederhana: minimal punya hak 'lihat' di modul Ekspor)
        $punyaIzin = HakAkses::where('id_user', $user->id_user)->exists();
        if (! $punyaIzin) {
            return response()->json(['error' => 'akses_ditolak'], 403);
        }

        // Catat aktivitas ekspor
        EksporLog::create([
            'id_user' => $user->id_user,
            'jenis_laporan' => $data['jenis'],
            'periode' => $data['periode'] ?? null,
            'format' => $data['format'],
            'waktu_unduh' => now(),
        ]);

        AuditLog::create([
            'id_user' => $user->id_user,
            'aksi' => 'ekspor_' . $data['format'],
            'modul' => 'Ekspor',
            'data_sesudah' => $data,
            'perangkat_ip' => $request->ip(),
        ]);

        // Stempel nama + waktu (di produksi: generate file PDF/Excel sungguhan)
        $stempel = $user->nama . ' · ' . now()->format('Y-m-d H:i');

        return response()->json([
            'url' => "/files/{$data['jenis']}-" . ($data['periode'] ?? 'all') . ".{$data['format']}",
            'stempel' => $stempel,
        ]);
    }
}
