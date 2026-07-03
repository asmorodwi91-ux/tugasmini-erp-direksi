<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('usulan_anggaran', function (Blueprint $t) {
            $t->id('id_usulan');
            $t->unsignedBigInteger('id_dept');
            $t->char('periode', 7);
            $t->decimal('plafon_diajukan', 18, 2);
            $t->unsignedBigInteger('diajukan_oleh');
            $t->unsignedBigInteger('disetujui_oleh')->nullable();
            $t->enum('status', ['menunggu','approved','revisi'])->default('menunggu');
            $t->string('catatan', 255)->nullable();
            $t->dateTime('waktu_putusan')->nullable();
            $t->dateTime('created_at')->useCurrent();
            $t->foreign('id_dept')->references('id_dept')->on('departemen');
            $t->foreign('diajukan_oleh')->references('id_user')->on('app_user');
            $t->foreign('disetujui_oleh')->references('id_user')->on('app_user');
            $t->unique(['id_dept','periode'], 'uq_usulan');
            $t->index(['status','periode'], 'idx_usulan_status');
        });
    }
    public function down(): void { Schema::dropIfExists('usulan_anggaran'); }
};
