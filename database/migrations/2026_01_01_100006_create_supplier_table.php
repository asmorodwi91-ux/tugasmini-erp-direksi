<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('supplier', function (Blueprint $t) {
            $t->id('id_supplier');
            $t->string('nama_supplier', 120);
            $t->string('alamat', 255)->nullable();
            $t->string('kontak', 80)->nullable();
            $t->decimal('skor_supplier', 4, 2)->nullable();
        });
    }
    public function down(): void { Schema::dropIfExists('supplier'); }
};
