<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MiniErpSeeder extends Seeder
{
    public function run(): void
    {
        // ROLE
        DB::table('role')->insert([
            ['nama_role'=>'Direktur Utama','level_akses'=>'setujui','keterangan'=>'Pantau KPI & setujui PO besar'],
            ['nama_role'=>'Wakil Direktur','level_akses'=>'setujui','keterangan'=>'Pengganti approval'],
            ['nama_role'=>'Manager Keuangan','level_akses'=>'ubah','keterangan'=>'Suplai data P&L & cash flow'],
            ['nama_role'=>'Manager IT','level_akses'=>'ubah','keterangan'=>'Kelola keamanan & hak akses'],
        ]);

        // USER (password: "password" — hash bcrypt)
        DB::table('app_user')->insert([
            ['nama'=>'Budi Santoso','email'=>'budi@perusahaan.co.id','password_hash'=>bcrypt('password'),'id_role'=>1],
            ['nama'=>'Sari Wijaya','email'=>'sari@perusahaan.co.id','password_hash'=>bcrypt('password'),'id_role'=>2],
            ['nama'=>'Lina Hartono','email'=>'lina@perusahaan.co.id','password_hash'=>bcrypt('password'),'id_role'=>3],
            ['nama'=>'Andi Pratama','email'=>'andi@perusahaan.co.id','password_hash'=>bcrypt('password'),'id_role'=>4],
        ]);

        // MODUL
        DB::table('modul')->insert([
            ['nama_modul'=>'Dashboard','deskripsi'=>'Executive dashboard'],
            ['nama_modul'=>'Approval','deskripsi'=>'Persetujuan PO besar'],
            ['nama_modul'=>'Keuangan','deskripsi'=>'Laporan keuangan'],
            ['nama_modul'=>'Operasional','deskripsi'=>'Kinerja operasional'],
            ['nama_modul'=>'Ekspor','deskripsi'=>'Ekspor laporan'],
        ]);

        // DEPARTEMEN
        DB::table('departemen')->insert([
            ['nama_dept'=>'Procurement','kepala_dept'=>'Andi'],
            ['nama_dept'=>'Keuangan','kepala_dept'=>'Lina'],
            ['nama_dept'=>'Produksi','kepala_dept'=>'Joko'],
        ]);

        // SUPPLIER
        DB::table('supplier')->insert([
            ['nama_supplier'=>'CV Maju','alamat'=>'Jl. A No.1','skor_supplier'=>4.50],
            ['nama_supplier'=>'PT Sentosa Abadi','alamat'=>'Jl. B No.2','skor_supplier'=>3.80],
        ]);

        // PURCHASE ORDER
        DB::table('purchase_order')->insert([
            ['no_po'=>'PO-001','tanggal'=>'2026-01-05','nilai_po'=>5000000,'id_supplier'=>1,'id_dept'=>1,'status_po'=>'menunggu','dibuat_oleh'=>'Andi'],
            ['no_po'=>'PO-002','tanggal'=>'2026-01-06','nilai_po'=>45000000,'id_supplier'=>2,'id_dept'=>1,'status_po'=>'menunggu','dibuat_oleh'=>'Andi'],
        ]);

        // PO ITEM
        DB::table('po_item')->insert([
            ['no_po'=>'PO-001','nama_item'=>'Kertas A4','qty'=>10,'harga_satuan'=>50000],
            ['no_po'=>'PO-001','nama_item'=>'Tinta','qty'=>5,'harga_satuan'=>200000],
            ['no_po'=>'PO-002','nama_item'=>'Laptop','qty'=>3,'harga_satuan'=>15000000],
        ]);

        // LAPORAN KEUANGAN (laba dihitung otomatis oleh DB)
        DB::table('laporan_keuangan')->insert([
            ['id_dept'=>2,'periode'=>'2026-01','pemasukan'=>1200000000,'pengeluaran'=>880000000,'persen_anggaran'=>72.00,'indikator_warna'=>'kuning'],
        ]);

        // KINERJA OPERASIONAL
        DB::table('kinerja_operasional')->insert([
            ['id_dept'=>1,'periode'=>'2026-01','kategori'=>'pembelian','nilai_aktual'=>92,'target'=>90,'skor'=>92],
            ['id_dept'=>3,'periode'=>'2026-01','kategori'=>'produksi','nilai_aktual'=>78,'target'=>95,'skor'=>78],
        ]);

        // AGING PIUTANG
        DB::table('aging_piutang')->insert([
            ['id_dept'=>2,'periode'=>'2026-01','jenis'=>'piutang','bucket_umur'=>'0-30','jumlah'=>90000000],
            ['id_dept'=>2,'periode'=>'2026-01','jenis'=>'hutang','bucket_umur'=>'0-30','jumlah'=>120000000],
        ]);
    }
}
