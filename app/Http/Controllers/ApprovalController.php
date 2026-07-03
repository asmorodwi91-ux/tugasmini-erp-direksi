<?php

namespace App\Http\Controllers;

use App\Models\PurchaseOrder;
use App\Models\Approval;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class ApprovalController extends Controller
{
    // GET /api/po/{id}
    public function show(string $id)
    {
        $po = PurchaseOrder::with(['items', 'supplier', 'departemen'])->find($id);
        if (! $po) {
            return response()->json(['error' => 'po_not_found'], 404);
        }

        return response()->json([
            'id' => $po->no_po,
            'supplier' => $po->supplier->nama_supplier ?? null,
            'nilai' => $po->nilai_po,
            'departemen' => $po->departemen->nama_dept ?? null,
            'status' => $po->status_po,
            'items' => $po->items->map(fn ($i) => [
                'nama' => $i->nama_item,
                'qty' => $i->qty,
                'subtotal' => $i->qty * $i->harga_satuan,
            ]),
        ]);
    }

    // POST /api/po/{id}/decision
    public function decision(Request $request, string $id)
    {
        $data = $request->validate([
            'keputusan' => 'required|in:setuju,tolak',
            'catatan' => 'nullable|string|max:255',
        ]);

        // Catatan wajib bila menolak
        if ($data['keputusan'] === 'tolak' && empty($data['catatan'])) {
            return response()->json(['error' => 'catatan_wajib_saat_menolak'], 422);
        }

        $po = PurchaseOrder::find($id);
        if (! $po) {
            return response()->json(['error' => 'po_not_found'], 404);
        }

        $po->status_po = $data['keputusan'] === 'setuju' ? 'approved' : 'rejected';
        $po->save();

        // Catat keputusan + perangkat/IP
        Approval::create([
            'no_po' => $po->no_po,
            'id_user' => $request->user()->id_user,
            'keputusan' => $data['keputusan'],
            'catatan' => $data['catatan'] ?? null,
            'perangkat_ip' => $request->ip(),
            'status_link' => 'terpakai',
            'waktu_putusan' => now(),
        ]);

        // Jejak audit
        AuditLog::create([
            'id_user' => $request->user()->id_user,
            'aksi' => 'keputusan_po_' . $data['keputusan'],
            'modul' => 'Approval',
            'data_sesudah' => ['no_po' => $po->no_po, 'status' => $po->status_po],
            'perangkat_ip' => $request->ip(),
        ]);

        // Bila approved: dispatch event/job ke queue (po.approved) — fire & forget
        // dispatch(new \App\Jobs\SendApprovalNotification($po->no_po));

        return response()->json([
            'id' => $po->no_po,
            'status' => $po->status_po,
            'waktu' => now()->toIso8601String(),
        ]);
    }
}
