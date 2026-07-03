<?php

namespace App\Http\Controllers;

use App\Models\LaporanKeuangan;
use App\Models\PurchaseOrder;
use App\Models\Notifikasi;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    // GET /api/dashboard?periode=2026-01
    public function index(Request $request)
    {
        $periode = $request->query('periode', now()->format('Y-m'));

        $keu = LaporanKeuangan::where('periode', $periode)->first();
        $poPending = PurchaseOrder::where('status_po', 'menunggu')->count();

        $earlyWarning = Notifikasi::where('level_kritis', 'kritis')
            ->where('dibaca', false)
            ->latest('id_notif')->take(5)
            ->get(['jenis as level', 'pesan']);

        return response()->json([
            'periode' => $periode,
            'kpi' => [
                'pendapatan' => $keu?->pemasukan ?? 0,
                'laba' => $keu?->laba ?? 0,
                'po_pending' => $poPending,
                'pemakaian_anggaran' => $keu?->persen_anggaran ?? 0,
            ],
            'indikator_anggaran' => $keu?->indikator_warna,
            'early_warning' => $earlyWarning,
        ]);
    }
}
