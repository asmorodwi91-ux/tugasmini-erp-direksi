<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void {
        DB::unprepared("
            CREATE TRIGGER trg_audit_no_update
            BEFORE UPDATE ON audit_log
            FOR EACH ROW
            BEGIN
                SIGNAL SQLSTATE '45000'
                SET MESSAGE_TEXT = 'audit_log bersifat permanen: UPDATE tidak diizinkan.';
            END
        ");
        DB::unprepared("
            CREATE TRIGGER trg_audit_no_delete
            BEFORE DELETE ON audit_log
            FOR EACH ROW
            BEGIN
                SIGNAL SQLSTATE '45000'
                SET MESSAGE_TEXT = 'audit_log bersifat permanen: DELETE tidak diizinkan.';
            END
        ");
    }
    public function down(): void {
        DB::unprepared("DROP TRIGGER IF EXISTS trg_audit_no_update");
        DB::unprepared("DROP TRIGGER IF EXISTS trg_audit_no_delete");
    }
};
