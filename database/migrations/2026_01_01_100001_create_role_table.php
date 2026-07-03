<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('role', function (Blueprint $t) {
            $t->id('id_role');
            $t->string('nama_role', 50)->unique();
            $t->enum('level_akses', ['lihat','ubah','setujui'])->default('lihat');
            $t->string('keterangan', 255)->nullable();
        });
    }
    public function down(): void { Schema::dropIfExists('role'); }
};
