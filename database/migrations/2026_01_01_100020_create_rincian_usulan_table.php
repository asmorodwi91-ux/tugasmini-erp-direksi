<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('rincian_usulan', function (Blueprint $t) {
            $t->id('id_rincian');
            $t->unsignedBigInteger('id_usulan');
            $t->string('pos_anggaran', 80);
            $t->decimal('realisasi_lalu', 18, 2)->default(0);
            $t->decimal('plafon_diajukan', 18, 2)->default(0);
            $t->foreign('id_usulan')->references('id_usulan')->on('usulan_anggaran')->cascadeOnDelete();
            $t->index('id_usulan', 'idx_rincian_usulan');
        });
    }
    public function down(): void { Schema::dropIfExists('rincian_usulan'); }
};
