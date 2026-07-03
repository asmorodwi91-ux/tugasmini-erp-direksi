<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('audit_log', function (Blueprint $t) {
            $t->bigIncrements('id_log');
            $t->unsignedBigInteger('id_user')->nullable();
            $t->string('aksi', 80);
            $t->string('modul', 80)->nullable();
            $t->json('data_sebelum')->nullable();
            $t->json('data_sesudah')->nullable();
            $t->string('perangkat_ip', 60)->nullable();
            $t->dateTime('created_at')->useCurrent();
            $t->foreign('id_user')->references('id_user')->on('app_user');
            $t->index(['id_user','created_at'], 'idx_audit_user');
            $t->index(['modul','created_at'], 'idx_audit_modul');
        });
    }
    public function down(): void { Schema::dropIfExists('audit_log'); }
};
