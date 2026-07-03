<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('po_item', function (Blueprint $t) {
            $t->string('no_po', 20);
            $t->string('nama_item', 120);
            $t->integer('qty');
            $t->decimal('harga_satuan', 15, 2);
            $t->primary(['no_po','nama_item']);
            $t->foreign('no_po')->references('no_po')->on('purchase_order')->cascadeOnDelete();
        });
    }
    public function down(): void { Schema::dropIfExists('po_item'); }
};
