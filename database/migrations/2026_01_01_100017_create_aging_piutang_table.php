<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('aging_piutang', function (Blueprint $t) {
            $t->id('id_aging');
            $t->unsignedBigInteger('id_dept');
            $t->char('periode', 7);
            $t->enum('jenis', ['hutang','piutang']);
            $t->enum('bucket_umur', ['0-30','31-60','61-90','>90']);
            $t->decimal('jumlah', 18, 2)->default(0);
            $t->foreign('id_dept')->references('id_dept')->on('departemen');
            $t->unique(['id_dept','periode','jenis','bucket_umur'], 'uq_aging');
            $t->index(['id_dept','periode'], 'idx_aging_periode');
        });
    }
    public function down(): void { Schema::dropIfExists('aging_piutang'); }
};
