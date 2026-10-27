# Praktikum Pemrograman Web - Ujian Tengah Semester (UTS)
## Tipe C: Sistem Data Produk UMKM

Repository ini berisi implementasi proyek Ujian Tengah Semester (UTS) mata kuliah **Praktikum Pemrograman Web** (INF11197) berbasis kerangka kerja **Laravel** dan basis data **MySQL**.

---

### Identitas Mahasiswa
- **NIM**: 2401020031
- **Mata Kuliah**: Praktikum Pemrograman Web (INF11197)
- **Tipe Soal**: Tipe C (Sistem Data Produk UMKM)
- **Basis Data**: `produk_umkm_2401020031`
- **Route Utama**: `/produk`

---

### Struktur Basis Data & Relasi
Aplikasi menggunakan relasi satu-ke-banyak (*one-to-many*) antara entitas kategori dan produk dengan integritas referensial:

1. **Tabel Master (`kategori_produk`)**:
   - `id`: `BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY`
   - `nama_kategori`: `VARCHAR(100) NOT NULL UNIQUE`

2. **Tabel Utama (`produk`)**:
   - `id`: `BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY`
   - `kode_produk`: `CHAR(10) NOT NULL UNIQUE`
   - `nama_produk`: `VARCHAR(100) NOT NULL`
   - `sku`: `VARCHAR(30) NOT NULL UNIQUE`
   - `stok`: `SMALLINT UNSIGNED NOT NULL CHECK (stok BETWEEN 0 AND 500)`
   - `kategori_produk_id`: `BIGINT UNSIGNED NOT NULL` (Foreign Key ke `kategori_produk.id`, `ON UPDATE CASCADE`, `ON DELETE RESTRICT`)
   - `created_at` & `updated_at`: `TIMESTAMP NULL`

---

### Daftar Route CRUD

| Method | URI | Nama Route | Deskripsi |
|---|---|---|---|
| `GET` | `/produk` | `produk.index` | Halaman indeks utama (tabel data, form tambah, form cari & filter) |
| `POST` | `/produk` | `produk.store` | Menyimpan data produk baru dengan validasi dan sanitasi |
| `GET` | `/produk/{id}/edit` | `produk.edit` | Menampilkan formulir ubah data produk |
| `PUT` | `/produk/{id}` | `produk.update` | Memperbarui data produk terpilih dengan method spoofing |
| `DELETE` | `/produk/{id}` | `produk.destroy` | Menghapus data produk terpilih dengan method spoofing & CSRF |

---

### Fitur Keamanan dan Implementasi Teknis
1. **PDO dan Prepared Statement**:
   Seluruh query yang menerima parameter input pengguna diakses melalui instans PDO (`DB::connection()->getPdo()`) dengan `prepare()` dan `execute()`, mencegah kerentanan *SQL Injection*.
2. **Error Handling Terstruktur**:
   Semua operasi basis data dibungkus dalam blok `try–catch`. Galat dicatat ke log melalui fungsi `report($error)`, sementara respons yang ditampilkan kepada pengguna berupa pesan kesalahan yang aman dan ramah pengguna.
3. **Sanitasi dan Validasi Server-Side**:
   - Sanitasi teks menggunakan `trim()` dan `strip_tags()`.
   - Validasi: `kode_produk` tepat 10 karakter alfanumerik; `nama_produk` 3–100 karakter; `sku` 5–30 karakter; `stok` integer 0–500; `kategori_produk_id` harus valid dan tersedia.
   - Pencegahan duplikasi kode atau SKU baik saat operasi Create maupun Update data lain.
4. **Fungsi PHP Sederhana & Tampilan Blade**:
   Fungsi `statusStok(int $stok)` menampilkan `'Tersedia'` jika stok > 0 dan `'Habis'` jika stok = 0. Ditampilkan pada template Blade murni tanpa framework CSS eksternal (Bootstrap/Tailwind) sesuai ketentuan.

---

### Sepuluh Skenario Pengujian Wajib (Hasil Uji PHPUnit)
Seluruh 10 skenario pengujian telah diuji dan lulus otomatis pada `tests/Feature/ProdukTest.php`:
1. **Read**: Tiga data awal dan nama kategori tampil pada indeks.
2. **Create valid**: Data baru tersimpan dan feedback keberhasilan muncul.
3. **Create tidak valid**: Pesan kesalahan tampil dan input lama dipertahankan (`old()`).
4. **Duplikat**: Kode atau SKU yang sama ditolak tanpa merusak data yang ada.
5. **Update**: Data lama tampil pada form edit dan perubahan berhasil disimpan.
6. **Konflik Update**: Nilai unik milik data produk lain ditolak.
7. **Delete**: Data terpilih terhapus melalui form DELETE dengan proteksi CSRF.
8. **Pencarian**: Kata kunci pencarian memeriksa tiga kolom (`kode_produk`, `nama_produk`, `sku`).
9. **Filter**: Hasil daftar produk dapat dibatasi berdasarkan kategori produk.
10. **Error handling**: Kesalahan koneksi/query dicatat dengan `report()`, pengguna menerima pesan aman.

---

### Panduan Instalasi & Menjalankan Proyek

1. **Clone repositori**:
   ```bash
   git clone https://github.com/cesoqt/Praktikum-Pemrograman-Web-Modul-1.git
   cd Praktikum-Pemrograman-Web-Modul-1
   ```

2. **Salin konfigurasi environment**:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

3. **Konfigurasi basis data di `.env`**:
   ```ini
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=produk_umkm_2401020031
   DB_USERNAME=root
   DB_PASSWORD=
   ```

4. **Migrasi dan Data Awal**:
   ```bash
   php artisan migrate:fresh --seed
   ```
   *Atau mengimpor berkas SQL yang disediakan:*
   ```bash
   mysql -u root produk_umkm_2401020031 < produk_umkm_2401020031.sql
   ```

5. **Jalankan Pengujian Otomatis**:
   ```bash
   php artisan test
   ```

6. **Jalankan Server**:
   ```bash
   php artisan serve
   ```
   Buka browser pada alamat: `http://localhost:8000/produk`

---

### Riwayat Commit Terstruktur (UTS)
1. `feat: migrasi tabel kategori_produk dan produk beserta data awal seeder`
2. `feat: implementasi read data produk, relasi kategori, dan fungsi statusStok`
3. `feat: implementasi create produk dengan validasi server-side dan sanitasi`
4. `feat: implementasi update dan delete produk dengan method spoofing dan pencegahan duplikat`
5. `feat: implementasi pengujian komprehensif, ekspor basis data SQL, dan dokumentasi README`
