<?php

namespace App\Http\Controllers;

use App\Models\KinerjaOperasional;
use Illuminate\Http\Request;

class OperationsController extends Controller
{
    // Deskripsi metrik utama per kategori (ditampilkan di kolom "Metrik Utama")
    private const METRIK = [
        'pembelian' => 'Belanja vs budget · skor supplier',
        'gudang'    => 'Akurasi stok · hasil QC',
        'penjualan' => 'Pendapatan vs target',
        'produksi'  => 'Hasil vs rencana',
        'sdm'       => 'Tingkat kehadiran',
    ];

    // GET /api/ops/scorecard?periode=2026-01
    public function scorecard(Request $request)
    {
        $periode = $request->query('periode', now()->format('Y-m'));

        $nilaiSkor = fn ($aktual, $target) => $target > 0 ? round($aktual / $target * 100, 1) : 0;
        $grade = fn ($p) => $p >= 90 ? 'A' : ($p >= 75 ? 'B' : 'C');

        $rows = KinerjaOperasional::with('departemen')
            ->where('periode', $periode)
            ->orderBy('id_dept')
            ->get();

        $departemen = $rows->map(function ($r) use ($nilaiSkor, $grade) {
            $persen = $nilaiSkor($r->nilai_aktual, $r->target);
            return [
                'nama' => $r->departemen->nama_dept ?? $r->kategori,
                'kategori' => $r->kategori,
                'metrik' => self::METRIK[$r->kategori] ?? '-',
                'aktual' => (float) $r->nilai_aktual,
                'target' => (float) $r->target,
                'persen' => $persen,
                'skor' => $grade($persen),
            ];
        })->values();

        // Tren skor total (rata-rata persen semua dept) untuk 6 bulan terakhir
        $periodeList = KinerjaOperasional::where('periode', '<=', $periode)
            ->distinct()
            ->orderBy('periode', 'desc')
            ->limit(6)
            ->pluck('periode')
            ->sort()
            ->values();

        $tren = $periodeList->map(function ($p) use ($nilaiSkor) {
            $rows = KinerjaOperasional::where('periode', $p)->get();
            $rata = $rows->count()
                ? round($rows->avg(fn ($r) => $nilaiSkor($r->nilai_aktual, $r->target)), 1)
                : 0;
            return ['periode' => $p, 'skor' => $rata];
        });

        return response()->json([
            'periode' => $periode,
            'departemen' => $departemen,
            'tren' => $tren,
        ]);
    }
}
