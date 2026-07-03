<?php

namespace App\Http\Controllers;

use App\Models\EksporLog;
use App\Models\AuditLog;
use App\Models\HakAkses;
use App\Models\LaporanKeuangan;
use App\Models\AgingPiutang;
use App\Models\KinerjaOperasional;
use App\Models\PurchaseOrder;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Http\Request;

class ExportController extends Controller
{
    // POST /api/export — menghasilkan file laporan (PDF / CSV) berisi data DB
    public function create(Request $request)
    {
        $data = $request->validate([
            'jenis' => 'required|in:keuangan,operasional,approval',
            'periode' => 'nullable|string',
            'format' => 'required|in:pdf,excel',
            'id_dept' => 'nullable',
        ]);

        $user = $request->user();

        // Cek izin (minimal punya hak akses pada modul apa pun)
        if (! HakAkses::where('id_user', $user->id_user)->exists()) {
            return response()->json(['error' => 'akses_ditolak'], 403);
        }

        $periode = $data['periode'] ?? now()->format('Y-m');
        $idDept = ($data['id_dept'] ?? null) && $data['id_dept'] !== 'semua' ? (int) $data['id_dept'] : null;
        $stempel = $user->nama . ' · ' . now()->format('Y-m-d H:i');

        [$judul, $sections] = $this->buildReport($data['jenis'], $periode, $idDept);

        // Catat aktivitas ekspor + jejak audit
        EksporLog::create([
            'id_user' => $user->id_user,
            'jenis_laporan' => $data['jenis'],
            'periode' => $periode,
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

        $namaFile = $data['jenis'] . '-' . $periode;

        return $data['format'] === 'excel'
            ? $this->downloadCsv($namaFile, $judul, $sections, $stempel)
            : $this->downloadPdf($namaFile, $judul, $sections, $stempel);
    }

    // GET /api/export/history — riwayat unduh (10 terbaru)
    public function history(Request $request)
    {
        $labelJenis = [
            'keuangan' => 'Laporan Keuangan',
            'operasional' => 'Kinerja Operasional',
            'approval' => 'Riwayat Approval',
        ];

        $rows = EksporLog::with('user')
            ->latest('waktu_unduh')
            ->limit(10)
            ->get()
            ->map(fn ($e) => [
                'id' => $e->id_ekspor,
                'laporan' => ($labelJenis[$e->jenis_laporan] ?? ucfirst($e->jenis_laporan))
                    . ($e->periode ? ' · ' . $e->periode : ''),
                'format' => $e->format === 'excel' ? 'XLSX' : 'PDF',
                'waktu' => optional($e->waktu_unduh)->format('d M H:i'),
                'oleh' => $e->user->nama ?? null,
            ]);

        return response()->json(['data' => $rows]);
    }

    /**
     * Susun isi laporan dari database.
     * @return array{0:string,1:array<int,array{heading:string,columns:array,rows:array}>}
     */
    private function buildReport(string $jenis, string $periode, ?int $idDept): array
    {
        $rp = fn ($n) => 'Rp ' . number_format((float) $n, 0, ',', '.');

        if ($jenis === 'operasional') {
            $rows = KinerjaOperasional::with('departemen')
                ->where('periode', $periode)
                ->when($idDept, fn ($q) => $q->where('id_dept', $idDept))
                ->get()
                ->map(function ($r) {
                    $persen = $r->target > 0 ? round($r->nilai_aktual / $r->target * 100, 1) : 0;
                    $skor = $persen >= 90 ? 'A' : ($persen >= 75 ? 'B' : 'C');
                    return [
                        $r->departemen->nama_dept ?? '-',
                        ucfirst($r->kategori),
                        rtrim(rtrim(number_format($r->nilai_aktual, 2), '0'), '.'),
                        rtrim(rtrim(number_format($r->target, 2), '0'), '.'),
                        $persen . '% (' . $skor . ')',
                    ];
                })->toArray();

            return ['Laporan Kinerja Operasional — ' . $periode, [
                ['heading' => 'Skor Kinerja per Departemen', 'columns' => ['Departemen', 'Kategori', 'Aktual', 'Target', 'Skor'], 'rows' => $rows],
            ]];
        }

        if ($jenis === 'approval') {
            $rows = PurchaseOrder::with(['supplier', 'departemen'])
                ->where('tanggal', 'like', $periode . '%')
                ->when($idDept, fn ($q) => $q->where('id_dept', $idDept))
                ->orderBy('tanggal')
                ->get()
                ->map(fn ($po) => [
                    $po->no_po,
                    optional($po->tanggal)->format('d-m-Y'),
                    $po->supplier->nama_supplier ?? '-',
                    $po->departemen->nama_dept ?? '-',
                    $rp($po->nilai_po),
                    ucfirst($po->status_po),
                    $po->dibuat_oleh ?? '-',
                ])->toArray();

            return ['Laporan Riwayat Purchase Order — ' . $periode, [
                ['heading' => 'Daftar Purchase Order', 'columns' => ['No. PO', 'Tanggal', 'Supplier', 'Departemen', 'Nilai', 'Status', 'Dibuat Oleh'], 'rows' => $rows],
            ]];
        }

        // keuangan (default)
        $keuRows = LaporanKeuangan::with('departemen')
            ->where('periode', $periode)
            ->when($idDept, fn ($q) => $q->where('id_dept', $idDept))
            ->get()
            ->map(fn ($r) => [
                $r->departemen->nama_dept ?? '-',
                $r->periode,
                $rp($r->pemasukan),
                $rp($r->pengeluaran),
                $rp($r->laba),
                number_format((float) $r->persen_anggaran, 2) . '%',
                ucfirst((string) $r->indikator_warna),
            ])->toArray();

        $agingRows = AgingPiutang::with('departemen')
            ->where('periode', $periode)
            ->when($idDept, fn ($q) => $q->where('id_dept', $idDept))
            ->orderBy('jenis')
            ->orderBy('bucket_umur')
            ->get()
            ->map(fn ($r) => [
                $r->departemen->nama_dept ?? '-',
                ucfirst($r->jenis),
                $r->bucket_umur . ' hari',
                $rp($r->jumlah),
            ])->toArray();

        return ['Laporan Keuangan — ' . $periode, [
            ['heading' => 'Ringkasan Laba/Rugi & Anggaran', 'columns' => ['Departemen', 'Periode', 'Pemasukan', 'Pengeluaran', 'Laba', '% Anggaran', 'Indikator'], 'rows' => $keuRows],
            ['heading' => 'Aging Hutang-Piutang', 'columns' => ['Departemen', 'Jenis', 'Umur', 'Jumlah'], 'rows' => $agingRows],
        ]];
    }

    private function downloadCsv(string $nama, string $judul, array $sections, string $stempel)
    {
        $out = fopen('php://temp', 'r+');
        fwrite($out, "\xEF\xBB\xBF"); // BOM agar Excel membaca UTF-8

        fputcsv($out, [$judul]);
        fputcsv($out, ['Dicetak oleh', $stempel]);
        fputcsv($out, []);

        foreach ($sections as $s) {
            fputcsv($out, [$s['heading']]);
            fputcsv($out, $s['columns']);
            if (empty($s['rows'])) {
                fputcsv($out, ['(tidak ada data)']);
            }
            foreach ($s['rows'] as $row) {
                fputcsv($out, $row);
            }
            fputcsv($out, []);
        }

        rewind($out);
        $content = stream_get_contents($out);
        fclose($out);

        return response($content, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $nama . '.csv"',
        ]);
    }

    private function downloadPdf(string $nama, string $judul, array $sections, string $stempel)
    {
        $html = '<html><head><meta charset="utf-8"><style>'
            . 'body{font-family:DejaVu Sans,sans-serif;color:#1A1F24;font-size:10px;}'
            . 'h1{font-size:16px;margin:0 0 2px;color:#1F4E79;}'
            . '.meta{font-size:9px;color:#697682;margin-bottom:12px;}'
            . 'h2{font-size:12px;margin:16px 0 6px;color:#163A5A;border-bottom:1px solid #D8DEE4;padding-bottom:3px;}'
            . 'table{width:100%;border-collapse:collapse;margin-bottom:8px;}'
            . 'th{background:#DBE7F1;color:#163A5A;text-align:left;padding:5px 6px;font-size:9px;border:1px solid #C7D6E6;}'
            . 'td{padding:4px 6px;border:1px solid #E8ECF0;font-size:9px;}'
            . 'tr:nth-child(even) td{background:#F4F6F8;}'
            . '</style></head><body>';
        $html .= '<h1>' . e($judul) . '</h1>';
        $html .= '<div class="meta">Mini ERP · Modul Direksi · Dicetak oleh ' . e($stempel) . '</div>';

        foreach ($sections as $s) {
            $html .= '<h2>' . e($s['heading']) . '</h2><table><thead><tr>';
            foreach ($s['columns'] as $c) {
                $html .= '<th>' . e($c) . '</th>';
            }
            $html .= '</tr></thead><tbody>';
            if (empty($s['rows'])) {
                $html .= '<tr><td colspan="' . count($s['columns']) . '">Tidak ada data untuk filter ini.</td></tr>';
            }
            foreach ($s['rows'] as $row) {
                $html .= '<tr>';
                foreach ($row as $cell) {
                    $html .= '<td>' . e($cell) . '</td>';
                }
                $html .= '</tr>';
            }
            $html .= '</tbody></table>';
        }
        $html .= '</body></html>';

        $options = new Options();
        $options->set('isRemoteEnabled', false);
        $options->set('defaultFont', 'DejaVu Sans');

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        return response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $nama . '.pdf"',
        ]);
    }
}
