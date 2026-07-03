<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('notifikasi', function (Blueprint $t) {
            $t->bigIncrements('id_notif');
            $t->unsignedBigInteger('id_user');
            $t->string('jenis', 50);
            $t->string('pesan', 255);
            $t->enum('level_kritis', ['info','warning','kritis'])->default('warning');
            $t->string('sumber_modul', 80)->nullable();
            $t->boolean('dibaca')->default(false);
            $t->dateTime('created_at')->useCurrent();
            $t->foreign('id_user')->references('id_user')->on('app_user');
            $t->index(['id_user','dibaca'], 'idx_notif_user');
        });
    }
    public function down(): void { Schema::dropIfExists('notifikasi'); }
};
