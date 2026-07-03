<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('hak_akses', function (Blueprint $t) {
            $t->id('id_akses');
            $t->unsignedBigInteger('id_user');
            $t->unsignedBigInteger('id_modul');
            $t->enum('level', ['lihat','ubah','setujui'])->default('lihat');
            $t->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate();
            $t->unique(['id_user','id_modul'], 'uq_hakakses');
            $t->foreign('id_user')->references('id_user')->on('app_user');
            $t->foreign('id_modul')->references('id_modul')->on('modul');
        });
    }
    public function down(): void { Schema::dropIfExists('hak_akses'); }
};
