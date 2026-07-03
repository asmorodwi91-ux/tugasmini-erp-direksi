<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MiniErpSeeder extends Seeder
{
    /**
     * Data simulasi realistis Mini ERP - Modul Direksi.
     * Data master (role, user, modul, departemen) bersifat fixed,
     * sedangkan sebagian purchase order memakai fake() untuk variasi.
     */
    public function run(): void
    {
        // ============================================================
        // 1. ROLE  (level_akses: lihat | ubah | setujui)
        // ============================================================
        DB::table('role')->insert([
            ['nama_role' => 'Direktur Utama',    'level_akses' => 'setujui', 'keterangan' => 'Pantau KPI & setujui PO besar / anggaran'],
            ['nama_role' => 'Wakil Direktur',    'level_akses' => 'setujui', 'keterangan' => 'Pengganti approval saat Direktur berhalangan'],
            ['nama_role' => 'Manager Keuangan',  'level_akses' => 'ubah',    'keterangan' => 'Suplai data P&L, arus kas & anggaran'],
            ['nama_role' => 'Manager IT',        'level_akses' => 'ubah',    'keterangan' => 'Kelola keamanan sistem & hak akses'],
        ]);

        // ============================================================
        // 2. USER  (password: "password")
        // ============================================================
        DB::table('app_user')->insert([
            ['nama' => 'Budi Santoso', 'email' => 'budi@perusahaan.co.id', 'password_hash' => bcrypt('password'), 'id_role' => 1],
            ['nama' => 'Sari Wijaya',  'email' => 'sari@perusahaan.co.id', 'password_hash' => bcrypt('password'), 'id_role' => 2],
            ['nama' => 'Lina Hartono', 'email' => 'lina@perusahaan.co.id', 'password_hash' => bcrypt('password'), 'id_role' => 3],
            ['nama' => 'Andi Pratama', 'email' => 'andi@perusahaan.co.id', 'password_hash' => bcrypt('password'), 'id_role' => 4],
        ]);

        // ============================================================
        // 3. MODUL
        // ============================================================
        DB::table('modul')->insert([
            ['nama_modul' => 'Dashboard',   'deskripsi' => 'Ringkasan eksekutif & KPI'],
            ['nama_modul' => 'Approval',    'deskripsi' => 'Persetujuan Purchase Order besar'],
            ['nama_modul' => 'Keuangan',    'deskripsi' => 'Laporan keuangan & aging'],
            ['nama_modul' => 'Operasional', 'deskripsi' => 'Kinerja operasional / scorecard'],
            ['nama_modul' => 'Ekspor',      'deskripsi' => 'Ekspor laporan (PDF / Excel)'],
        ]);

        // ============================================================
        // 4. HAK AKSES (per user x modul)
        // ============================================================
        $hakAkses = [];
        $modulIds = [1, 2, 3, 4, 5];
        // Direktur & Wakil: setujui semua
        foreach ([1, 2] as $uid) {
            foreach ($modulIds as $mid) {
                $hakAkses[] = ['id_user' => $uid, 'id_modul' => $mid, 'level' => 'setujui'];
            }
        }
        // Manager Keuangan: ubah keuangan/operasional/ekspor, lihat sisanya
        foreach ($modulIds as $mid) {
            $hakAkses[] = ['id_user' => 3, 'id_modul' => $mid, 'level' => in_array($mid, [3, 4, 5]) ? 'ubah' : 'lihat'];
        }
        // Manager IT: ubah semua modul
        foreach ($modulIds as $mid) {
            $hakAkses[] = ['id_user' => 4, 'id_modul' => $mid, 'level' => 'ubah'];
        }
        DB::table('hak_akses')->insert($hakAkses);

        // ============================================================
        // 5. DEPARTEMEN (divisi nyata + kepala)
        //    id: 1=Pembelian 2=Gudang 3=Penjualan 4=Produksi 5=SDM
        // ============================================================
        DB::table('departemen')->insert([
            ['nama_dept' => 'Pembelian', 'kepala_dept' => 'Rudi Hermawan'],
            ['nama_dept' => 'Gudang',    'kepala_dept' => 'Dedi Kurniawan'],
            ['nama_dept' => 'Penjualan', 'kepala_dept' => 'Maya Anggraini'],
            ['nama_dept' => 'Produksi',  'kepala_dept' => 'Joko Susilo'],
            ['nama_dept' => 'SDM',       'kepala_dept' => 'Rina Melati'],
        ]);

        // ============================================================
        // 6. SUPPLIER
        // ============================================================
        DB::table('supplier')->insert([
            ['nama_supplier' => 'PT Sinar Baja Sentosa',       'alamat' => 'Kawasan Industri MM2100, Blok C-12, Cikarang, Bekasi',   'kontak' => '021-8990-1122 / procurement@sinarbaja.co.id', 'skor_supplier' => 4.60],
            ['nama_supplier' => 'CV Mitra Logistik Nusantara',  'alamat' => 'Jl. Rungkut Industri Raya No. 45, Surabaya',            'kontak' => '031-8471-5566 / sales@mitralogistik.id',     'skor_supplier' => 4.10],
            ['nama_supplier' => 'PT Andalan Mesin Industri',    'alamat' => 'Jl. Gatot Subroto KM 7, Jatiuwung, Tangerang',          'kontak' => '021-5525-7788 / info@andalanmesin.com',      'skor_supplier' => 3.75],
            ['nama_supplier' => 'PT Sumber Pangan Makmur',      'alamat' => 'Jl. Kaligawe Raya No. 88, Genuk, Semarang',             'kontak' => '024-6580-3344 / order@sumberpangan.co.id',   'skor_supplier' => 4.25],
            ['nama_supplier' => 'CV Karya Teknik Mandiri',      'alamat' => 'Jl. Soekarno-Hatta No. 210, Kiaracondong, Bandung',     'kontak' => '022-7301-9900 / cs@karyateknik.id',          'skor_supplier' => 3.90],
        ]);

        // ============================================================
        // 7. PURCHASE ORDER + PO ITEM
        //    8 PO fixed (status & nilai beragam, sebar antar dept)
        //    + 4 PO acak (fake()) untuk variasi
        // ============================================================
        $poFixed = [
            ['no_po' => 'PO-2026-001', 'tanggal' => '2026-01-08', 'id_supplier' => 1, 'id_dept' => 1, 'status_po' => 'approved', 'dibuat_oleh' => 'Rudi Hermawan',
                'items' => [
                    ['nama_item' => 'Baja Lembaran 3mm', 'qty' => 100, 'harga_satuan' => 850000],
                    ['nama_item' => 'Cat Anti Karat 20L', 'qty' => 50, 'harga_satuan' => 180000],
                ]],
            ['no_po' => 'PO-2026-002', 'tanggal' => '2026-01-12', 'id_supplier' => 3, 'id_dept' => 4, 'status_po' => 'menunggu', 'dibuat_oleh' => 'Joko Susilo',
                'items' => [
                    ['nama_item' => 'Mesin CNC Bubut', 'qty' => 1, 'harga_satuan' => 450000000],
                    ['nama_item' => 'Motor Servo 2kW', 'qty' => 4, 'harga_satuan' => 12500000],
                ]],
            ['no_po' => 'PO-2026-003', 'tanggal' => '2026-01-15', 'id_supplier' => 2, 'id_dept' => 2, 'status_po' => 'approved', 'dibuat_oleh' => 'Dedi Kurniawan',
                'items' => [
                    ['nama_item' => 'Forklift Elektrik 2 Ton', 'qty' => 2, 'harga_satuan' => 185000000],
                    ['nama_item' => 'Pallet Kayu Standar', 'qty' => 200, 'harga_satuan' => 150000],
                ]],
            ['no_po' => 'PO-2026-004', 'tanggal' => '2026-02-02', 'id_supplier' => 5, 'id_dept' => 3, 'status_po' => 'rejected', 'dibuat_oleh' => 'Maya Anggraini',
                'items' => [
                    ['nama_item' => 'Kendaraan Box Isuzu Elf', 'qty' => 1, 'harga_satuan' => 320000000],
                ]],
            ['no_po' => 'PO-2026-005', 'tanggal' => '2026-02-10', 'id_supplier' => 4, 'id_dept' => 1, 'status_po' => 'approved', 'dibuat_oleh' => 'Rudi Hermawan',
                'items' => [
                    ['nama_item' => 'Tepung Terigu 25kg', 'qty' => 400, 'harga_satuan' => 210000],
                    ['nama_item' => 'Gula Rafinasi 50kg', 'qty' => 100, 'harga_satuan' => 620000],
                ]],
            ['no_po' => 'PO-2026-006', 'tanggal' => '2026-03-05', 'id_supplier' => 5, 'id_dept' => 5, 'status_po' => 'menunggu', 'dibuat_oleh' => 'Rina Melati',
                'items' => [
                    ['nama_item' => 'Laptop Dell Latitude 5450', 'qty' => 10, 'harga_satuan' => 14500000],
                    ['nama_item' => 'Seragam Karyawan (set)', 'qty' => 300, 'harga_satuan' => 185000],
                ]],
            ['no_po' => 'PO-2026-007', 'tanggal' => '2026-03-18', 'id_supplier' => 3, 'id_dept' => 4, 'status_po' => 'menunggu', 'dibuat_oleh' => 'Joko Susilo',
                'items' => [
                    ['nama_item' => 'Genset Diesel 100 kVA', 'qty' => 1, 'harga_satuan' => 275000000],
                    ['nama_item' => 'Kompresor Udara 10HP', 'qty' => 2, 'harga_satuan' => 42500000],
                ]],
            ['no_po' => 'PO-2026-008', 'tanggal' => '2026-04-01', 'id_supplier' => 2, 'id_dept' => 2, 'status_po' => 'approved', 'dibuat_oleh' => 'Dedi Kurniawan',
                'items' => [
                    ['nama_item' => 'Rak Besi Heavy Duty', 'qty' => 30, 'harga_satuan' => 3200000],
                    ['nama_item' => 'Hand Pallet 3 Ton', 'qty' => 10, 'harga_satuan' => 6500000],
                ]],
        ];

        foreach ($poFixed as $po) {
            $nilai = array_sum(array_map(fn ($i) => $i['qty'] * $i['harga_satuan'], $po['items']));
            DB::table('purchase_order')->insert([
                'no_po' => $po['no_po'], 'tanggal' => $po['tanggal'], 'nilai_po' => $nilai,
                'id_supplier' => $po['id_supplier'], 'id_dept' => $po['id_dept'],
                'status_po' => $po['status_po'], 'dibuat_oleh' => $po['dibuat_oleh'],
            ]);
            foreach ($po['items'] as $item) {
                DB::table('po_item')->insert([
                    'no_po' => $po['no_po'], 'nama_item' => $item['nama_item'],
                    'qty' => $item['qty'], 'harga_satuan' => $item['harga_satuan'],
                ]);
            }
        }

        // --- 4 PO acak untuk variasi (fake) ---
        $itemPool = [
            ['Kertas A4 80gsm (rim)', 55000], ['Toner Printer Laserjet', 1250000],
            ['Sarung Tangan Safety (lusin)', 95000], ['Helm Proyek SNI', 85000],
            ['Oli Hidrolik 20L', 780000], ['Bearing Industri SKF', 425000],
            ['Conveyor Belt (meter)', 340000], ['Panel Listrik 3 Phase', 8500000],
            ['AC Split 2PK', 5200000], ['CCTV IP Camera 4MP', 1850000],
            ['Meja Kerja Kantor', 1350000], ['Kursi Ergonomis', 1750000],
        ];
        $statusPool = ['menunggu', 'approved', 'rejected', 'draft'];
        for ($n = 9; $n <= 12; $n++) {
            $noPo = sprintf('PO-2026-%03d', $n);
            $dept = fake()->numberBetween(1, 5);
            $items = fake()->randomElements($itemPool, fake()->numberBetween(1, 3));
            $poItems = [];
            foreach ($items as $it) {
                $poItems[] = ['nama_item' => $it[0], 'qty' => fake()->numberBetween(2, 60), 'harga_satuan' => $it[1]];
            }
            $nilai = array_sum(array_map(fn ($i) => $i['qty'] * $i['harga_satuan'], $poItems));
            DB::table('purchase_order')->insert([
                'no_po' => $noPo,
                'tanggal' => fake()->dateTimeBetween('2026-01-05', '2026-06-25')->format('Y-m-d'),
                'nilai_po' => $nilai,
                'id_supplier' => fake()->numberBetween(1, 5),
                'id_dept' => $dept,
                'status_po' => fake()->randomElement($statusPool),
                'dibuat_oleh' => fake()->randomElement(['Rudi Hermawan', 'Dedi Kurniawan', 'Maya Anggraini', 'Joko Susilo', 'Rina Melati']),
            ]);
            foreach ($poItems as $item) {
                DB::table('po_item')->insert(array_merge(['no_po' => $noPo], $item));
            }
        }

        // ============================================================
        // 8. APPROVAL (jejak keputusan PO yang sudah diputuskan)
        // ============================================================
        DB::table('approval')->insert([
            ['no_po' => 'PO-2026-001', 'id_user' => 1, 'keputusan' => 'setuju', 'catatan' => 'Sesuai kebutuhan produksi Q1.', 'perangkat_ip' => '103.20.14.55', 'status_link' => 'terpakai', 'waktu_putusan' => '2026-01-09 09:15:00'],
            ['no_po' => 'PO-2026-003', 'id_user' => 1, 'keputusan' => 'setuju', 'catatan' => 'Peremajaan alat gudang disetujui.', 'perangkat_ip' => '103.20.14.55', 'status_link' => 'terpakai', 'waktu_putusan' => '2026-01-16 10:40:00'],
            ['no_po' => 'PO-2026-004', 'id_user' => 1, 'keputusan' => 'tolak', 'catatan' => 'Harga di atas benchmark pasar, minta penawaran ulang.', 'perangkat_ip' => '103.20.14.55', 'status_link' => 'terpakai', 'waktu_putusan' => '2026-02-03 14:05:00'],
            ['no_po' => 'PO-2026-005', 'id_user' => 2, 'keputusan' => 'setuju', 'catatan' => 'Stok bahan baku menipis, disetujui.', 'perangkat_ip' => '103.20.14.72', 'status_link' => 'terpakai', 'waktu_putusan' => '2026-02-11 08:30:00'],
            ['no_po' => 'PO-2026-008', 'id_user' => 1, 'keputusan' => 'setuju', 'catatan' => 'Ekspansi kapasitas penyimpanan.', 'perangkat_ip' => '103.20.14.55', 'status_link' => 'terpakai', 'waktu_putusan' => '2026-04-02 11:20:00'],
        ]);

        // ============================================================
        // 9. LAPORAN KEUANGAN (laba = generated column, JANGAN diisi)
        //    pemasukan = alokasi anggaran, pengeluaran = realisasi
        //    indikator: hijau <70, kuning 70-90, merah >90
        //    Diisi 11 bulan (2025-08 s.d. 2026-06) agar tren 6 bulan
        //    di dashboard memakai data real. Bulan 2026-01 & 2026-06
        //    dikunci (showcase) agar KPI konsisten.
        // ============================================================
        $indikator = fn ($p) => $p < 70 ? 'hijau' : ($p <= 90 ? 'kuning' : 'merah');

        // Nilai showcase (id_dept => [pemasukan, pengeluaran, persen]); urutan penting:
        // Penjualan (3) didahulukan agar menjadi headline KPI dashboard.
        $showcase = [
            '2026-01' => [
                3 => [2500000000, 2375000000, 95.00],
                4 => [3100000000, 2232000000, 72.00],
                1 => [1200000000, 1044000000, 87.00],
                2 => [680000000,  442000000,  65.00],
                5 => [540000000,  356400000,  66.00],
            ],
            '2026-06' => [
                3 => [2800000000, 2464000000, 88.00],
                4 => [3300000000, 3201000000, 97.00],
                1 => [1350000000, 810000000,  60.00],
                2 => [720000000,  590400000,  82.00],
                5 => [580000000,  452400000,  78.00],
            ],
        ];

        // Alokasi anggaran dasar bulanan per departemen (untuk bulan non-showcase)
        $deptBase = [3 => 2350000000, 4 => 3000000000, 1 => 1150000000, 2 => 650000000, 5 => 520000000];

        // Data historis 24 bulan (2 tahun ke belakang) s.d. 2026-06.
        // Bulan 2026-01 & 2026-06 dikunci (showcase) agar KPI konsisten.
        $totalBulan = 24;
        $akhir = \Carbon\Carbon::createFromFormat('Y-m', '2026-06')->startOfMonth();
        $bulan = [];
        for ($k = $totalBulan - 1; $k >= 0; $k--) {
            $bulan[] = $akhir->copy()->subMonths($k)->format('Y-m');
        }
        $jumlahBulan = count($bulan);

        foreach ($bulan as $i => $periode) {
            if (isset($showcase[$periode])) {
                foreach ($showcase[$periode] as $idDept => [$pemasukan, $pengeluaran, $persen]) {
                    DB::table('laporan_keuangan')->insert([
                        'id_dept' => $idDept, 'periode' => $periode,
                        'pemasukan' => $pemasukan, 'pengeluaran' => $pengeluaran,
                        'persen_anggaran' => $persen, 'indikator_warna' => $indikator($persen),
                    ]);
                }
                continue;
            }

            // Bulan lain: pertumbuhan bertahap (fraksi rentang) + variasi realisasi (fake)
            $frac = $jumlahBulan > 1 ? $i / ($jumlahBulan - 1) : 1;
            $growth = 0.70 + 0.35 * $frac;
            foreach ([3, 4, 1, 2, 5] as $idDept) {
                $pemasukan = (int) round($deptBase[$idDept] * $growth);
                $persen = fake()->randomFloat(2, 62, 94);
                $pengeluaran = (int) round($pemasukan * $persen / 100);
                DB::table('laporan_keuangan')->insert([
                    'id_dept' => $idDept, 'periode' => $periode,
                    'pemasukan' => $pemasukan, 'pengeluaran' => $pengeluaran,
                    'persen_anggaran' => $persen, 'indikator_warna' => $indikator($persen),
                ]);
            }
        }

        // ============================================================
        // 10. KINERJA OPERASIONAL (5 dept, kategori sesuai divisi)
        //     Diisi 24 bulan (2 tahun) agar tren skor memakai data real.
        //     Bulan 2026-01 & 2026-06 dikunci (showcase). skor = persen aktual/target.
        // ============================================================
        $skor = fn ($a, $t) => $t > 0 ? round($a / $t * 100, 2) : 0;

        // [id_dept, kategori, target]
        $deptKategori = [
            [1, 'pembelian', 90],
            [2, 'gudang',    95],
            [3, 'penjualan', 100],
            [4, 'produksi',  100],
            [5, 'sdm',       100],
        ];

        // Nilai aktual showcase per periode (id_dept => aktual)
        $kinerjaShowcase = [
            '2026-01' => [1 => 92, 2 => 80, 3 => 96, 4 => 70, 5 => 85],
            '2026-06' => [1 => 95, 2 => 91, 3 => 88, 4 => 82, 5 => 72],
        ];

        foreach ($bulan as $i => $periode) {
            foreach ($deptKategori as [$idDept, $kategori, $target]) {
                if (isset($kinerjaShowcase[$periode])) {
                    $aktual = $kinerjaShowcase[$periode][$idDept];
                } else {
                    // Naik bertahap (fraksi rentang) + variasi kecil, dibatasi maks 108
                    $frac = $jumlahBulan > 1 ? $i / ($jumlahBulan - 1) : 1;
                    $aktual = min(108, (int) round($target * (0.72 + 0.28 * $frac) + fake()->numberBetween(-4, 5)));
                }
                DB::table('kinerja_operasional')->insert([
                    'id_dept' => $idDept, 'periode' => $periode, 'kategori' => $kategori,
                    'nilai_aktual' => $aktual, 'target' => $target, 'skor' => $skor($aktual, $target),
                ]);
            }
        }

        // ============================================================
        // 11. AGING HUTANG-PIUTANG (semua bucket terisi, tidak ada Rp 0)
        //     piutang: dept Penjualan (3) | hutang: dept Pembelian (1)
        // ============================================================
        DB::table('aging_piutang')->insert([
            // 2026-01
            ['id_dept' => 3, 'periode' => '2026-01', 'jenis' => 'piutang', 'bucket_umur' => '0-30',  'jumlah' => 1250000000],
            ['id_dept' => 3, 'periode' => '2026-01', 'jenis' => 'piutang', 'bucket_umur' => '31-60', 'jumlah' => 480000000],
            ['id_dept' => 3, 'periode' => '2026-01', 'jenis' => 'piutang', 'bucket_umur' => '61-90', 'jumlah' => 215000000],
            ['id_dept' => 3, 'periode' => '2026-01', 'jenis' => 'piutang', 'bucket_umur' => '>90',   'jumlah' => 95000000],
            ['id_dept' => 1, 'periode' => '2026-01', 'jenis' => 'hutang',  'bucket_umur' => '0-30',  'jumlah' => 890000000],
            ['id_dept' => 1, 'periode' => '2026-01', 'jenis' => 'hutang',  'bucket_umur' => '31-60', 'jumlah' => 340000000],
            ['id_dept' => 1, 'periode' => '2026-01', 'jenis' => 'hutang',  'bucket_umur' => '61-90', 'jumlah' => 165000000],
            ['id_dept' => 1, 'periode' => '2026-01', 'jenis' => 'hutang',  'bucket_umur' => '>90',   'jumlah' => 72000000],
            // 2026-06
            ['id_dept' => 3, 'periode' => '2026-06', 'jenis' => 'piutang', 'bucket_umur' => '0-30',  'jumlah' => 1420000000],
            ['id_dept' => 3, 'periode' => '2026-06', 'jenis' => 'piutang', 'bucket_umur' => '31-60', 'jumlah' => 510000000],
            ['id_dept' => 3, 'periode' => '2026-06', 'jenis' => 'piutang', 'bucket_umur' => '61-90', 'jumlah' => 240000000],
            ['id_dept' => 3, 'periode' => '2026-06', 'jenis' => 'piutang', 'bucket_umur' => '>90',   'jumlah' => 118000000],
            ['id_dept' => 1, 'periode' => '2026-06', 'jenis' => 'hutang',  'bucket_umur' => '0-30',  'jumlah' => 950000000],
            ['id_dept' => 1, 'periode' => '2026-06', 'jenis' => 'hutang',  'bucket_umur' => '31-60', 'jumlah' => 385000000],
            ['id_dept' => 1, 'periode' => '2026-06', 'jenis' => 'hutang',  'bucket_umur' => '61-90', 'jumlah' => 190000000],
            ['id_dept' => 1, 'periode' => '2026-06', 'jenis' => 'hutang',  'bucket_umur' => '>90',   'jumlah' => 88000000],
        ]);

        // ============================================================
        // 12. NOTIFIKASI / EARLY WARNING (untuk user id 1)
        // ============================================================
        DB::table('notifikasi')->insert([
            ['id_user' => 1, 'jenis' => 'stok_rendah',      'pesan' => 'Stok Baja Lembaran 3mm di Gudang Utama tersisa 8% dari titik minimum reorder.',        'level_kritis' => 'warning', 'sumber_modul' => 'Operasional', 'dibaca' => false, 'created_at' => now()->subDays(2)],
            ['id_user' => 1, 'jenis' => 'anggaran_kritis',  'pesan' => 'Pemakaian anggaran Departemen Penjualan mencapai 95% dari plafon periode 2026-01.',       'level_kritis' => 'kritis',  'sumber_modul' => 'Keuangan',    'dibaca' => false, 'created_at' => now()->subHours(6)],
            ['id_user' => 1, 'jenis' => 'po_tertunda',      'pesan' => 'PO-2026-002 senilai Rp 500 jt menunggu persetujuan lebih dari 4 jam.',                   'level_kritis' => 'kritis',  'sumber_modul' => 'Approval',    'dibaca' => false, 'created_at' => now()->subHours(5)],
            ['id_user' => 1, 'jenis' => 'transaksi_janggal','pesan' => 'Terdeteksi transaksi tidak wajar: indikasi pembayaran ganda ke PT Andalan Mesin Industri.','level_kritis' => 'kritis',  'sumber_modul' => 'Keuangan',    'dibaca' => false, 'created_at' => now()->subHour()],
            ['id_user' => 1, 'jenis' => 'anggaran_warning', 'pesan' => 'Pemakaian anggaran Departemen Gudang telah melewati 80% dari plafon periode 2026-06.',    'level_kritis' => 'warning', 'sumber_modul' => 'Keuangan',    'dibaca' => false, 'created_at' => now()->subHours(12)],
        ]);

        // ============================================================
        // 13. USULAN ANGGARAN (status beragam) + RINCIAN POS ANGGARAN
        //     plafon_diajukan usulan = SUM(rincian.plafon_diajukan)
        // ============================================================
        $usulanData = [
            [
                'usulan' => ['id_dept' => 4, 'periode' => '2026-02', 'diajukan_oleh' => 3, 'disetujui_oleh' => null, 'status' => 'menunggu', 'catatan' => null, 'waktu_putusan' => null, 'created_at' => '2026-02-05 09:20:00'],
                'rincian' => [
                    ['pos_anggaran' => 'Bahan Baku',   'realisasi_lalu' => 480000000, 'plafon_diajukan' => 520000000],
                    ['pos_anggaran' => 'Tenaga Kerja', 'realisasi_lalu' => 220000000, 'plafon_diajukan' => 240000000],
                    ['pos_anggaran' => 'Operasional',  'realisasi_lalu' => 80000000,  'plafon_diajukan' => 90000000],
                ],
            ],
            [
                'usulan' => ['id_dept' => 1, 'periode' => '2026-02', 'diajukan_oleh' => 3, 'disetujui_oleh' => 1, 'status' => 'approved', 'catatan' => 'Disetujui sesuai rencana pengadaan bahan baku Q1.', 'waktu_putusan' => now()->subDays(10), 'created_at' => '2026-02-03 10:15:00'],
                'rincian' => [
                    ['pos_anggaran' => 'Pengadaan Bahan', 'realisasi_lalu' => 600000000, 'plafon_diajukan' => 650000000],
                    ['pos_anggaran' => 'Logistik',        'realisasi_lalu' => 180000000, 'plafon_diajukan' => 190000000],
                    ['pos_anggaran' => 'Operasional',     'realisasi_lalu' => 55000000,  'plafon_diajukan' => 60000000],
                ],
            ],
            [
                'usulan' => ['id_dept' => 5, 'periode' => '2026-02', 'diajukan_oleh' => 3, 'disetujui_oleh' => 2, 'status' => 'revisi', 'catatan' => 'Mohon rinci komponen biaya pelatihan & rekrutmen sebelum disetujui.', 'waktu_putusan' => now()->subDays(7), 'created_at' => '2026-02-04 14:40:00'],
                'rincian' => [
                    ['pos_anggaran' => 'Rekrutmen',   'realisasi_lalu' => 150000000, 'plafon_diajukan' => 200000000],
                    ['pos_anggaran' => 'Pelatihan',   'realisasi_lalu' => 180000000, 'plafon_diajukan' => 260000000],
                    ['pos_anggaran' => 'Operasional', 'realisasi_lalu' => 170000000, 'plafon_diajukan' => 190000000],
                ],
            ],
        ];

        foreach ($usulanData as $u) {
            $plafonTotal = array_sum(array_column($u['rincian'], 'plafon_diajukan'));
            $idUsulan = DB::table('usulan_anggaran')->insertGetId(
                array_merge($u['usulan'], ['plafon_diajukan' => $plafonTotal])
            );
            foreach ($u['rincian'] as $r) {
                DB::table('rincian_usulan')->insert(array_merge(['id_usulan' => $idUsulan], $r));
            }
        }
    }
}
