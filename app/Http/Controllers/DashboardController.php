<?php

namespace App\Http\Controllers;

use App\Models\LaporanKeuangan;
use App\Models\KinerjaOperasional;
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

        // Kinerja per departemen (nama asli dari DB) untuk periode terpilih
        $kinerja = KinerjaOperasional::with('departemen')
            ->where('periode', $periode)
            ->get()
            ->map(fn ($r) => [
                'nama' => $r->departemen->nama_dept ?? $r->kategori,
                'kategori' => $r->kategori,
                'aktual' => (float) $r->nilai_aktual,
                'target' => (float) $r->target,
                'persen' => $r->target > 0 ? round($r->nilai_aktual / $r->target * 100, 1) : 0,
            ])
            ->values();

        // Tren pendapatan & laba: 6 bulan terakhir s.d. periode terpilih (agregat semua dept)
        $tren = LaporanKeuangan::selectRaw('periode, SUM(pemasukan) as pendapatan, SUM(laba) as laba')
            ->where('periode', '<=', $periode)
            ->groupBy('periode')
            ->orderBy('periode', 'desc')
            ->limit(6)
            ->get()
            ->sortBy('periode')
            ->values()
            ->map(fn ($r) => [
                'periode' => $r->periode,
                'pendapatan' => (float) $r->pendapatan,
                'laba' => (float) $r->laba,
            ]);

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
            'kinerja' => $kinerja,
            'tren' => $tren,
        ]);
    }
}
