<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('purchase_order', function (Blueprint $t) {
            $t->string('no_po', 20)->primary();
            $t->date('tanggal');
            $t->decimal('nilai_po', 15, 2);
            $t->unsignedBigInteger('id_supplier');
            $t->unsignedBigInteger('id_dept');
            $t->enum('status_po', ['draft','menunggu','approved','rejected'])->default('menunggu');
            $t->string('dibuat_oleh', 100)->nullable();
            $t->dateTime('created_at')->useCurrent();
            $t->foreign('id_supplier')->references('id_supplier')->on('supplier');
            $t->foreign('id_dept')->references('id_dept')->on('departemen');
            $t->index('status_po', 'idx_po_status');
            $t->index('tanggal', 'idx_po_tanggal');
        });
    }
    public function down(): void { Schema::dropIfExists('purchase_order'); }
};
