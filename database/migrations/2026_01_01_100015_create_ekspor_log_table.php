<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('ekspor_log', function (Blueprint $t) {
            $t->bigIncrements('id_ekspor');
            $t->unsignedBigInteger('id_user');
            $t->string('jenis_laporan', 80);
            $t->char('periode', 7)->nullable();
            $t->enum('format', ['pdf','excel']);
            $t->dateTime('waktu_unduh')->useCurrent();
            $t->foreign('id_user')->references('id_user')->on('app_user');
        });
    }
    public function down(): void { Schema::dropIfExists('ekspor_log'); }
};
