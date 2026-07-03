<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('modul', function (Blueprint $t) {
            $t->id('id_modul');
            $t->string('nama_modul', 80)->unique();
            $t->string('deskripsi', 255)->nullable();
        });
    }
    public function down(): void { Schema::dropIfExists('modul'); }
};
