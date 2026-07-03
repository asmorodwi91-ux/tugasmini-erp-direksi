<?php

namespace App\Http\Controllers;

use App\Models\KinerjaOperasional;
use Illuminate\Http\Request;

class OperationsController extends Controller
{
    // GET /api/ops/scorecard?periode=2026-01
    public function scorecard(Request $request)
    {
        $periode = $request->query('periode', now()->format('Y-m'));

        $rows = KinerjaOperasional::with('departemen')
            ->where('periode', $periode)->get();

        $skor = fn ($aktual, $target) => $target > 0
            ? ($aktual / $target * 100 >= 90 ? 'A' : ($aktual / $target * 100 >= 75 ? 'B' : 'C'))
            : '-';

        return response()->json([
            'departemen' => $rows->map(fn ($r) => [
                'nama' => $r->departemen->nama_dept ?? $r->kategori,
                'kategori' => $r->kategori,
                'aktual' => $r->nilai_aktual,
                'target' => $r->target,
                'skor' => $skor($r->nilai_aktual, $r->target),
            ]),
        ]);
    }
}
