<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProdukSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $pdo = DB::connection()->getPdo();

        $stmtKategori = $pdo->prepare('INSERT INTO kategori_produk (nama_kategori) VALUES (:nama_kategori)');

        $kategoriList = ['Makanan', 'Minuman'];
        $kategoriIds = [];

        foreach ($kategoriList as $nama) {
            $stmtKategori->execute(['nama_kategori' => $nama]);
            $kategoriIds[$nama] = (int) $pdo->lastInsertId();
        }

        $now = date('Y-m-d H:i:s');
        $stmtProduk = $pdo->prepare(
            'INSERT INTO produk (kode_produk, nama_produk, sku, stok, kategori_produk_id, created_at, updated_at)
             VALUES (:kode_produk, :nama_produk, :sku, :stok, :kategori_produk_id, :created_at, :updated_at)'
        );

        $dataAwal = [
            [
                'kode_produk' => 'PR00000001',
                'nama_produk' => 'Keripik Gonggong',
                'sku' => 'SKU-MKN-001',
                'stok' => 40,
                'kategori_produk_id' => $kategoriIds['Makanan'],
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'kode_produk' => 'PR00000002',
                'nama_produk' => 'Kopi Bubuk Lokal',
                'sku' => 'SKU-MNM-001',
                'stok' => 25,
                'kategori_produk_id' => $kategoriIds['Minuman'],
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'kode_produk' => 'PR00000003',
                'nama_produk' => 'Kue Bangkit',
                'sku' => 'SKU-MKN-002',
                'stok' => 30,
                'kategori_produk_id' => $kategoriIds['Makanan'],
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        foreach ($dataAwal as $row) {
            $stmtProduk->execute($row);
        }
    }
}
