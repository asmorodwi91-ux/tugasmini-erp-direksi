<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('kinerja_operasional', function (Blueprint $t) {
            $t->id('id_kinerja');
            $t->unsignedBigInteger('id_dept');
            $t->char('periode', 7);
            $t->enum('kategori', ['pembelian','gudang','penjualan','produksi','sdm']);
            $t->decimal('nilai_aktual', 15, 2)->nullable();
            $t->decimal('target', 15, 2)->nullable();
            $t->decimal('skor', 5, 2)->nullable();
            $t->foreign('id_dept')->references('id_dept')->on('departemen');
            $t->unique(['id_dept','periode','kategori'], 'uq_kinerja');
        });
    }
    public function down(): void { Schema::dropIfExists('kinerja_operasional'); }
};
