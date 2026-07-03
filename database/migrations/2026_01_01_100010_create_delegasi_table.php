<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('delegasi', function (Blueprint $t) {
            $t->id('id_delegasi');
            $t->unsignedBigInteger('id_user_asal');
            $t->unsignedBigInteger('id_user_ganti');
            $t->string('jenis_transaksi', 80)->nullable();
            $t->date('tgl_mulai');
            $t->date('tgl_selesai');
            $t->boolean('is_aktif')->default(true);
            $t->foreign('id_user_asal')->references('id_user')->on('app_user');
            $t->foreign('id_user_ganti')->references('id_user')->on('app_user');
        });
    }
    public function down(): void { Schema::dropIfExists('delegasi'); }
};
