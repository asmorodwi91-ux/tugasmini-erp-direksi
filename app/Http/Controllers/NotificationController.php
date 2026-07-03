<?php

namespace App\Http\Controllers;

use App\Models\Notifikasi;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    // GET /api/notifications?level=semua
    public function index(Request $request)
    {
        $q = Notifikasi::where('id_user', $request->user()->id_user);

        if ($request->query('level') && $request->query('level') !== 'semua') {
            $q->where('level_kritis', $request->query('level'));
        }

        return response()->json([
            'data' => $q->latest('id_notif')->get(),
        ]);
    }

    // POST /api/notifications/{id}/read
    public function markRead(Request $request, int $id)
    {
        $notif = Notifikasi::where('id_notif', $id)
            ->where('id_user', $request->user()->id_user)->first();

        if (! $notif) {
            return response()->json(['error' => 'notif_not_found'], 404);
        }

        $notif->dibaca = true;
        $notif->save();

        return response()->json(['id' => $id, 'dibaca' => true]);
    }
}
