<?php

namespace App\Http\Controllers;

use App\Models\LaporanKeuangan;
use App\Models\AgingPiutang;
use Illuminate\Http\Request;

class FinanceController extends Controller
{
    // GET /api/finance/report?periode=2026-01
    public function report(Request $request)
    {
        $periode = $request->query('periode', now()->format('Y-m'));

        $keu = LaporanKeuangan::where('periode', $periode)->first();

        $aging = AgingPiutang::where('periode', $periode)->get()
            ->groupBy('jenis')
            ->map(fn ($rows) => $rows->pluck('jumlah', 'bucket_umur'));

        return response()->json([
            'pl' => $keu?->laba ?? 0,
            'arus_kas' => $keu?->pemasukan ?? 0,
            'pemakaian_anggaran' => $keu?->persen_anggaran ?? 0,
            'indikator_warna' => $keu?->indikator_warna,
            'aging' => [
                'piutang' => $aging['piutang'] ?? [],
                'hutang' => $aging['hutang'] ?? [],
            ],
        ]);
    }
}
