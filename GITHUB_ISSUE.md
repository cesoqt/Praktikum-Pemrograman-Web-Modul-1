# Laporan GitHub Issue (Dokumentasi Masalah & Perbaikan)

**Judul Issue**:  
`[Bug Fix] Mengatasi Kesalahan Migrasi 1071 (Specified key was too long) dan Penegakan Foreign Key / CHECK Constraint pada MySQL`

---

### 1. Masalah yang Terjadi (Problem Description)
Saat pertama kali menjalankan perintah migrasi database Laravel:
```bash
php artisan migrate
```
Muncul pesan kesalahan:
```
SQLSTATE[42000]: Syntax error or access violation: 1071 Specified key was too long; max key length is 1000 bytes (Connection: mysql, Host: 127.0.0.1, Port: 3306, Database: produk_umkm_2401020031, SQL: alter table `users` add unique `users_email_unique`(`email`))
```
Selain itu, tabel relational dengan Foreign Key (`ON UPDATE CASCADE`, `ON DELETE RESTRICT`) dan `CHECK (stok BETWEEN 0 AND 500)` membutuhkan storage engine yang mendukung integritas referensial.

---

### 2. Langkah Pemeriksaan (Investigation Steps)
1. Memeriksa konfigurasi koneksi database pada `.env` dan `config/database.php`.
2. Memeriksa variabel default storage engine pada MySQL menggunakan query PDO:
   ```sql
   SHOW VARIABLES LIKE 'default_storage_engine';
   ```
   **Temuan**: MySQL pada instalasi WampServer memiliki nilai `default_storage_engine` bernilai `MyISAM`. 
3. Mesin `MyISAM` memiliki batas panjang indeks gabungan maksimal 1.000 byte dan tidak mendukung integritas referensial Foreign Key (`ON UPDATE CASCADE`, `ON DELETE RESTRICT`) serta `CHECK constraint`.

---

### 3. Langkah Perbaikan (Fix / Solution)
1. **Mengonfigurasi Engine InnoDB secara Eksplisit**:
   Pada berkas `config/database.php`, nilai `'engine' => null` pada koneksi `mysql` diubah menjadi:
   ```php
   'engine' => 'InnoDB',
   ```
2. **Mengatur Default String Length**:
   Pada berkas `app/Providers/AppServiceProvider.php`, menambahkan batasan panjang default string di method `boot()`:
   ```php
   use Illuminate\Support\Facades\Schema;

   public function boot(): void
   {
       Schema::defaultStringLength(191);
   }
   ```
3. **Memastikan Foreign Key dan CHECK Constraint**:
   Pada migrasi `create_produk_table.php`, foreign key dikonfigurasi:
   ```php
   $table->foreignId('kategori_produk_id')
       ->constrained('kategori_produk')
       ->cascadeOnUpdate()
       ->restrictOnDelete();
   ```
   Dan penambahan constraint `CHECK`:
   ```php
   if (DB::getDriverName() === 'mysql') {
       DB::statement('ALTER TABLE produk ADD CONSTRAINT chk_produk_stok CHECK (stok BETWEEN 0 AND 500)');
   }
   ```

---

### 4. Hasil Uji Ulang (Verification & Re-test)
1. Menjalankan ulang fresh migration dan seeder:
   ```bash
   php artisan migrate:fresh --seed
   ```
   *Hasil*: Seluruh 7 migrasi berhasil dieksekusi dengan status `DONE` tanpa error.
2. Memeriksa data awal:
   *Hasil*: 3 data awal wajib (*Keripik Gonggong*, *Kopi Bubuk Lokal*, *Kue Bangkit*) dan 2 data kategori (*Makanan*, *Minuman*) berhasil tersimpan.
3. Menjalankan test suite otomatis:
   ```bash
   php artisan test
   ```
   *Hasil*: 12 skenario pengujian (termasuk 10 uji wajib UTS) berhasil lulus (`12 passed, 62 assertions`).
