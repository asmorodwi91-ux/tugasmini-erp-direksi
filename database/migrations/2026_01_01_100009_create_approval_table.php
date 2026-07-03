<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('approval', function (Blueprint $t) {
            $t->id('id_approval');
            $t->string('no_po', 20);
            $t->unsignedBigInteger('id_user');
            $t->enum('keputusan', ['setuju','tolak'])->nullable();
            $t->string('catatan', 255)->nullable();
            $t->string('perangkat_ip', 60)->nullable();
            $t->string('link_token', 100)->nullable()->unique();
            $t->enum('status_link', ['aktif','terpakai','kadaluarsa'])->default('aktif');
            $t->dateTime('waktu_putusan')->nullable();
            $t->dateTime('created_at')->useCurrent();
            $t->foreign('no_po')->references('no_po')->on('purchase_order');
            $t->foreign('id_user')->references('id_user')->on('app_user');
            $t->index(['status_link','waktu_putusan'], 'idx_appr_status');
        });
    }
    public function down(): void { Schema::dropIfExists('approval'); }
};
