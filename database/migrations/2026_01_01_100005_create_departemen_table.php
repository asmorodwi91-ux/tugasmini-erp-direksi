<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('departemen', function (Blueprint $t) {
            $t->id('id_dept');
            $t->string('nama_dept', 80)->unique();
            $t->string('kepala_dept', 100)->nullable();
        });
    }
    public function down(): void { Schema::dropIfExists('departemen'); }
};
