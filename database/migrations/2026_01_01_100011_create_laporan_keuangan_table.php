<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('laporan_keuangan', function (Blueprint $t) {
            $t->id('id_laporan');
            $t->unsignedBigInteger('id_dept');
            $t->char('periode', 7);
            $t->decimal('pemasukan', 18, 2)->default(0);
            $t->decimal('pengeluaran', 18, 2)->default(0);
            // generated column: laba = pemasukan - pengeluaran (STORED)
            $t->decimal('laba', 18, 2)->storedAs('pemasukan - pengeluaran');
            $t->decimal('persen_anggaran', 5, 2)->nullable();
            $t->enum('indikator_warna', ['hijau','kuning','merah'])->nullable();
            $t->foreign('id_dept')->references('id_dept')->on('departemen');
            $t->unique(['id_dept','periode'], 'uq_lapkeu');
        });
    }
    public function down(): void { Schema::dropIfExists('laporan_keuangan'); }
};
