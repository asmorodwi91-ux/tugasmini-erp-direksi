<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('otp_token', function (Blueprint $t) {
            $t->bigIncrements('id_otp');
            $t->unsignedBigInteger('id_user');
            $t->char('kode_otp', 6);
            $t->dateTime('dibuat_at')->useCurrent();
            $t->dateTime('expired_at');
            $t->boolean('is_used')->default(false);
            $t->foreign('id_user')->references('id_user')->on('app_user');
            $t->index(['id_user','expired_at'], 'idx_otp_user');
        });
    }
    public function down(): void { Schema::dropIfExists('otp_token'); }
};
