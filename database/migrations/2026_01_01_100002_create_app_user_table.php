<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('app_user', function (Blueprint $t) {
            $t->id('id_user');
            $t->string('nama', 100);
            $t->string('email', 150)->unique();
            $t->string('password_hash', 255);
            $t->unsignedBigInteger('id_role');
            $t->enum('status_2fa', ['aktif','nonaktif'])->default('aktif');
            $t->tinyInteger('failed_login')->default(0);
            $t->boolean('is_locked')->default(false);
            $t->boolean('is_active')->default(true);
            $t->dateTime('created_at')->useCurrent();
            $t->foreign('id_role')->references('id_role')->on('role');
        });
    }
    public function down(): void { Schema::dropIfExists('app_user'); }
};
