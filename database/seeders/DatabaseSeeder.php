<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Mengisi seluruh tabel Mini ERP - Modul Direksi
        // (role, user, modul, departemen, supplier, PO, laporan keuangan, dll)
        $this->call(\Database\Seeders\MiniErpSeeder::class);
    }
}