<?php

namespace App\Http\Controllers;

use App\Models\LaporanKeuangan;
use App\Models\AgingPiutang;
use App\Models\Departemen;
use Illuminate\Http\Request;

class FinanceController extends Controller
{
    // GET /api/finance/report?periode=2026-01&id_dept=3
    public function report(Request $request)
    {
        $periode = $request->query('periode', now()->format('Y-m'));
        $idDept = $request->query('id_dept'); // kosong / "semua" = seluruh departemen

        $filterDept = fn ($q) => ($idDept && $idDept !== 'semua')
            ? $q->where('id_dept', (int) $idDept)
            : $q;

        // Ringkasan keuangan (agregat bila semua dept)
        $agg = $filterDept(LaporanKeuangan::where('periode', $periode))
            ->selectRaw('SUM(pemasukan) AS pemasukan, SUM(pengeluaran) AS pengeluaran, SUM(laba) AS laba')
            ->first();

        $pemasukan   = (float) ($agg->pemasukan ?? 0);
        $pengeluaran = (float) ($agg->pengeluaran ?? 0);
        $laba        = (float) ($agg->laba ?? 0);
        $persen      = $pemasukan > 0 ? round($pengeluaran / $pemasukan * 100, 2) : 0;
        $indikator   = $persen == 0 ? null : ($persen < 70 ? 'hijau' : ($persen <= 90 ? 'kuning' : 'merah'));

        // Aging hutang-piutang (agregat per bucket)
        $aging = $filterDept(AgingPiutang::where('periode', $periode))
            ->selectRaw('jenis, bucket_umur, SUM(jumlah) AS jumlah')
            ->groupBy('jenis', 'bucket_umur')
            ->get()
            ->groupBy('jenis')
            ->map(fn ($rows) => $rows->pluck('jumlah', 'bucket_umur'));

        // Tren laba & arus kas: 6 bulan terakhir s.d. periode terpilih
        $tren = $filterDept(LaporanKeuangan::where('periode', '<=', $periode))
            ->selectRaw('periode, SUM(pemasukan) AS arus_kas, SUM(laba) AS laba')
            ->groupBy('periode')
            ->orderBy('periode', 'desc')
            ->limit(6)
            ->get()
            ->sortBy('periode')
            ->values()
            ->map(fn ($r) => [
                'periode' => $r->periode,
                'laba' => (float) $r->laba,
                'arus_kas' => (float) $r->arus_kas,
            ]);

        return response()->json([
            'periode' => $periode,
            'id_dept' => ($idDept && $idDept !== 'semua') ? (int) $idDept : null,
            'pl' => $laba,
            'arus_kas' => $pemasukan,
            'pengeluaran' => $pengeluaran,
            'pemakaian_anggaran' => $persen,
            'indikator_warna' => $indikator,
            'aging' => [
                'piutang' => $aging['piutang'] ?? [],
                'hutang' => $aging['hutang'] ?? [],
            ],
            'tren' => $tren,
            'departemen_list' => Departemen::orderBy('id_dept')->get(['id_dept', 'nama_dept']),
        ]);
    }
}
