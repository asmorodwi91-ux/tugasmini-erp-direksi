<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ApprovalController;
use App\Http\Controllers\FinanceController;
use App\Http\Controllers\OperationsController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\AccessController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\BudgetController;

// ---------------------------------------------------------------
// PUBLIK (tanpa token) — Autentikasi 2FA
// ---------------------------------------------------------------
Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/verify-otp', [AuthController::class, 'verifyOtp']);

// ---------------------------------------------------------------
// TERPROTEKSI (butuh token Sanctum)
// ---------------------------------------------------------------
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    // Modul 1 — Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index']);

    // Modul 2 — Approval PO
    Route::get('/po/{id}', [ApprovalController::class, 'show']);
    Route::post('/po/{id}/decision', [ApprovalController::class, 'decision']);

    // Modul 3 — Keuangan
    Route::get('/finance/report', [FinanceController::class, 'report']);

    // Modul 4 — Operasional
    Route::get('/ops/scorecard', [OperationsController::class, 'scorecard']);

    // Modul 5 — Notifikasi
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markRead']);

    // Modul 7 — Hak Akses
    Route::get('/access/users', [AccessController::class, 'users']);
    Route::post('/access/save', [AccessController::class, 'save']);

    // Modul 8 — Ekspor
    Route::post('/export', [ExportController::class, 'create']);

    // Modul 9 — Budget
    Route::post('/budget/proposal', [BudgetController::class, 'store']);
    Route::get('/budget/proposal/{id}', [BudgetController::class, 'show']);
    Route::post('/budget/decision', [BudgetController::class, 'decision']);
});
