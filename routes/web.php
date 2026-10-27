<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Validator;

// ==================================================
// PERTEMUAN 4 - LATIHAN PHP
// ==================================================

// Route::get('/latihan-php', function () {
//     $nama = 'Muhammad Al-Fikry Akbar';
//     $nilai = [60, 75, 55, 70, 80];

//     $hitungRataRata = function (array $data): float {
//         $total = 0;

//         foreach ($data as $angka) {
//             $total += $angka;
//         }

//         return $total / count($data);
//     };

//     $rataRata = $hitungRataRata($nilai);

//     if ($rataRata >= 75) {
//         $status = 'Lulus';
//     } else {
//         $status = 'Perlu Perbaikan';
//     }

//     return view('latihan-php', compact(
//         'nama',
//         'nilai',
//         'rataRata',
//         'status'
//     ));
// });

// ==================================================
// FORM MAHASISWA - PERTEMUAN SEBELUMNYA
// ==================================================

// Route::get('/form-mahasiswa', function () {
//     return view('form-mahasiswa');
// });

// Route::post('/form-mahasiswa', function (Request $request) {

//     $dataBersih = [
//         'nama' => strip_tags(
//             trim((string) $request->input('nama'))
//         ),

//         'NIM' => trim(
//             (string) $request->input('NIM')
//         ),

//         'email' => filter_var(
//             (string) $request->input('email'),
//             FILTER_SANITIZE_EMAIL
//         ),

//         'usia' => trim(
//             (string) $request->input('usia')
//         ),
//     ];

//     $validator = Validator::make($dataBersih, [
//         'nama' => ['required', 'min:3', 'max:50'],
//         'NIM' => ['required', 'digits_between:8,12'],
//         'email' => ['required', 'email'],
//         'usia' => ['required', 'integer', 'min:17', 'max:60'],
//     ], [
//         'nama.required' => 'Nama wajib diisi.',
//         'nama.min' => 'Nama minimal 3 karakter.',
//         'nama.max' => 'Nama maksimal 50 karakter.',

//         'NIM.required' => 'NIM wajib diisi.',
//         'NIM.digits_between' => 'NIM harus berisi 8–12 digit.',

//         'email.required' => 'Email wajib diisi.',
//         'email.email' => 'Format email tidak valid.',

//         'usia.required' => 'Usia wajib diisi.',
//         'usia.integer' => 'Usia harus berupa angka.',
//         'usia.min' => 'Usia minimal 17 tahun.',
//         'usia.max' => 'Usia maksimal 60 tahun.',
//     ]);

//         if ($validator->fails()) {
//             return back()
//                 ->withInput()
//                 ->withErrors($validator)
//                 ->with('gagal', 'Data tidak disimpan karena input tidak valid.');
//         }

//     $data = $validator->validated();
//     $data['usia'] = (int) $data['usia'];

//     return view('hasil-form', ['data' => $data]);
// });

// ==================================================
// PERTEMUAN 5 - READ MAHASISWA BERDASARKAN NIM
// ==================================================

// Route::get('/mahasiswa/{nim?}', function (?string $nim = null) {
//     try {
//         $pdo = DB::connection()->getPdo();

//         $sql = 'SELECT m.nim, m.nama, m.email, m.usia,
//                        p.nama_prodi
//                 FROM mahasiswa AS m
//                 JOIN program_studi AS p
//                   ON p.id = m.program_studi_id';

//         if ($nim !== null) {
//             $sql .= ' WHERE m.nim = :nim';
//         }

//         $sql .= ' ORDER BY m.nim';

//         $statement = $pdo->prepare($sql);

//         $statement->execute(
//             $nim !== null ? ['nim' => $nim] : []
//         );

//         $daftarMahasiswa = $statement->fetchAll(
//             \PDO::FETCH_ASSOC
//         );

//         return view('mahasiswa', [
//             'daftarMahasiswa' => $daftarMahasiswa,
//             'daftarProgramStudi' => [],
//             'nim' => $nim,
//         ]);

//     } catch (\Throwable $error) {
//         report($error);

//         return response(
//             'Koneksi atau query basis data gagal. Periksa file .env dan layanan MySQL.',
//             500
//         );
//     }
// });

// ==================================================
// PERTEMUAN 7 - CRUD UPDATE DELETE PENCARIAN & FILTER
// ==================================================

Route::get('/mahasiswa', function (Request $request) {
    $kataKunci = trim((string) $request->query('q', ''));
    $programStudiId = $request->query('program_studi_id', '');
    try {
        $pdo = DB::connection()->getPdo();
        $sql = 'SELECT m.id, m.nim, m.nama, m.email, m.usia,
                       m.program_studi_id, p.nama_prodi
                FROM mahasiswa AS m
                JOIN program_studi AS p
                  ON p.id = m.program_studi_id
                WHERE 1 = 1';
        $parameter = [];
        if ($kataKunci !== '') {
            $sql .= ' AND (m.nim LIKE :kata_kunci_nim
                      OR m.nama LIKE :kata_kunci_nama
                      OR m.email LIKE :kata_kunci_email)';
            $nilaiPencarian = '%'.$kataKunci.'%';
            $parameter['kata_kunci_nim'] = $nilaiPencarian;
            $parameter['kata_kunci_nama'] = $nilaiPencarian;
            $parameter['kata_kunci_email'] = $nilaiPencarian;
        }
        if ($programStudiId !== '') {
            $sql .= ' AND m.program_studi_id = :program_studi_id';
            $parameter['program_studi_id'] = (int) $programStudiId;
        }
        $sql .= ' ORDER BY m.nim';
        $ambilMahasiswa = $pdo->prepare($sql);
        $ambilMahasiswa->execute($parameter);
        $daftarMahasiswa = $ambilMahasiswa->fetchAll(PDO::FETCH_ASSOC);

        $ambilProdi = $pdo->prepare(
            'SELECT id, nama_prodi
             FROM program_studi
             ORDER BY nama_prodi'
        );
        $ambilProdi->execute();
        $daftarProgramStudi = $ambilProdi->fetchAll(PDO::FETCH_ASSOC);

        return view('mahasiswa', compact(
            'daftarMahasiswa',
            'daftarProgramStudi',
            'kataKunci',
            'programStudiId'
        ));
    } catch (Throwable $error) {
        report($error);

        return response(
            'Data tidak dapat dibaca. Periksa koneksi dan query.',
            500
        );
    }
})->name('mahasiswa.index');

Route::post('/mahasiswa', function (Request $request) {
    $data = $request->validate([
        'nim' => ['required', 'string', 'max:20'],
        'nama' => ['required', 'string', 'max:100'],
        'email' => ['required', 'email', 'max:100'],
        'usia' => ['required', 'integer', 'min:15', 'max:100'],
        'program_studi_id' => ['required', 'integer'],
    ]);
    try {
        $pdo = DB::connection()->getPdo();
        $cek = $pdo->prepare(
            'SELECT COUNT(*) FROM mahasiswa
             WHERE nim = :nim OR email = :email'
        );
        $cek->execute(['nim' => $data['nim'], 'email' => $data['email']]);
        if ((int) $cek->fetchColumn() > 0) {
            return back()->withInput()->with(
                'gagal',
                'NIM atau email sudah digunakan.'
            );
        }
        $simpan = $pdo->prepare(
            'INSERT INTO mahasiswa
             (nim, nama, email, usia, program_studi_id)
             VALUES (:nim, :nama, :email, :usia, :program_studi_id)'
        );
        $simpan->execute($data);

        return redirect()->route('mahasiswa.index')->with(
            'berhasil',
            'Data mahasiswa berhasil ditambahkan.'
        );
    } catch (Throwable $error) {
        report($error);

        return back()->withInput()->with(
            'gagal',
            'Data gagal disimpan. Periksa koneksi dan data.'
        );
    }
})->name('mahasiswa.store');

Route::get('/mahasiswa/{id}/edit', function (int $id) {
    try {
        $pdo = DB::connection()->getPdo();
        $ambilMahasiswa = $pdo->prepare(
            'SELECT id, nim, nama, email, usia, program_studi_id
             FROM mahasiswa WHERE id = :id'
        );
        $ambilMahasiswa->execute(['id' => $id]);
        $mahasiswa = $ambilMahasiswa->fetch(PDO::FETCH_ASSOC);
        if (! $mahasiswa) {
            return redirect()->route('mahasiswa.index')->with(
                'gagal',
                'Data mahasiswa tidak ditemukan.'
            );
        }
        $ambilProdi = $pdo->prepare(
            'SELECT id, nama_prodi FROM program_studi ORDER BY nama_prodi'
        );
        $ambilProdi->execute();
        $daftarProgramStudi = $ambilProdi->fetchAll(PDO::FETCH_ASSOC);

        return view('mahasiswa-edit', compact(
            'mahasiswa',
            'daftarProgramStudi'
        ));
    } catch (Throwable $error) {
        report($error);

        return redirect()->route('mahasiswa.index')->with(
            'gagal',
            'Data gagal dibuka untuk diubah.'
        );
    }
})->name('mahasiswa.edit');

Route::put('/mahasiswa/{id}', function (Request $request, int $id) {
    $data = $request->validate([
        'nim' => ['required', 'string', 'max:20'],
        'nama' => ['required', 'string', 'max:100'],
        'email' => ['required', 'email', 'max:100'],
        'usia' => ['required', 'integer', 'min:15', 'max:100'],
        'program_studi_id' => ['required', 'integer'],
    ]);
    try {
        $pdo = DB::connection()->getPdo();
        $cek = $pdo->prepare(
            'SELECT COUNT(*) FROM mahasiswa
             WHERE (nim = :nim OR email = :email)
               AND id <> :id'
        );
        $cek->execute([
            'nim' => $data['nim'],
            'email' => $data['email'],
            'id' => $id,
        ]);
        if ((int) $cek->fetchColumn() > 0) {
            return back()->withInput()->with(
                'gagal',
                'NIM atau email sudah digunakan mahasiswa lain.'
            );
        }
        $ubah = $pdo->prepare(
            'UPDATE mahasiswa
             SET nim = :nim, nama = :nama, email = :email,
                 usia = :usia, program_studi_id = :program_studi_id
             WHERE id = :id'
        );
        $ubah->execute([
            'nim' => $data['nim'],
            'nama' => $data['nama'],
            'email' => $data['email'],
            'usia' => $data['usia'],
            'program_studi_id' => $data['program_studi_id'],
            'id' => $id,
        ]);

        return redirect()->route('mahasiswa.index')->with(
            'berhasil',
            'Data mahasiswa berhasil diperbarui.'
        );
    } catch (Throwable $error) {
        report($error);

        return back()->withInput()->with(
            'gagal',
            'Data gagal diperbarui. Periksa koneksi dan data.'
        );
    }
})->name('mahasiswa.update');

Route::delete('/mahasiswa/{id}', function (int $id) {
    try {
        $pdo = DB::connection()->getPdo();
        $hapus = $pdo->prepare(
            'DELETE FROM mahasiswa WHERE id = :id'
        );
        $hapus->execute(['id' => $id]);
        if ($hapus->rowCount() === 0) {
            return redirect()->route('mahasiswa.index')->with(
                'gagal',
                'Data mahasiswa tidak ditemukan.'
            );
        }

        return redirect()->route('mahasiswa.index')->with(
            'berhasil',
            'Data mahasiswa berhasil dihapus.'
        );
    } catch (Throwable $error) {
        report($error);

        return redirect()->route('mahasiswa.index')->with(
            'gagal',
            'Data gagal dihapus. Periksa relasi atau koneksi.'
        );
    }
})->name('mahasiswa.destroy');

// ==================================================
// UTS PEMROGRAMAN WEB - TIPE C: SISTEM DATA PRODUK UMKM
// ==================================================

if (! function_exists('statusStok')) {
    /**
     * Menentukan status stok produk ('Tersedia' atau 'Habis').
     */
    function statusStok(int $stok): string
    {
        if ($stok > 0) {
            return 'Tersedia';
        }

        return 'Habis';
    }
}

Route::redirect('/', '/produk');

Route::get('/produk', function (Request $request) {
    $kataKunci = trim((string) $request->query('q', ''));
    $kategoriProdukId = $request->query('kategori_produk_id', '');

    try {
        $pdo = DB::connection()->getPdo();

        $ambilKategori = $pdo->prepare('SELECT id, nama_kategori FROM kategori_produk ORDER BY nama_kategori ASC');
        $ambilKategori->execute();
        $daftarKategori = $ambilKategori->fetchAll(PDO::FETCH_ASSOC);

        $sql = 'SELECT p.id, p.kode_produk, p.nama_produk, p.sku, p.stok, p.kategori_produk_id,
                       k.nama_kategori
                FROM produk AS p
                JOIN kategori_produk AS k ON k.id = p.kategori_produk_id';

        $conditions = [];
        $params = [];

        if ($kataKunci !== '') {
            $conditions[] = '(p.kode_produk LIKE :q1 OR p.nama_produk LIKE :q2 OR p.sku LIKE :q3)';
            $params['q1'] = '%'.$kataKunci.'%';
            $params['q2'] = '%'.$kataKunci.'%';
            $params['q3'] = '%'.$kataKunci.'%';
        }

        if ($kategoriProdukId !== '' && $kategoriProdukId !== null) {
            $conditions[] = 'p.kategori_produk_id = :kategori_produk_id';
            $params['kategori_produk_id'] = (int) $kategoriProdukId;
        }

        if (! empty($conditions)) {
            $sql .= ' WHERE '.implode(' AND ', $conditions);
        }

        $sql .= ' ORDER BY p.id ASC';

        $ambilProduk = $pdo->prepare($sql);
        $ambilProduk->execute($params);
        $daftarProduk = $ambilProduk->fetchAll(PDO::FETCH_ASSOC);

        return view('produk', compact('daftarProduk', 'daftarKategori', 'kataKunci', 'kategoriProdukId'));
    } catch (Throwable $error) {
        report($error);

        return response(
            'Koneksi atau query basis data gagal. Periksa file .env dan layanan MySQL.',
            500
        );
    }
})->name('produk.index');

Route::post('/produk', function (Request $request) {
    // Akan diimplementasikan pada commit Create
})->name('produk.store');

Route::get('/produk/{id}/edit', function (int $id) {
    // Akan diimplementasikan pada commit Update
})->name('produk.edit');

Route::put('/produk/{id}', function (Request $request, int $id) {
    // Akan diimplementasikan pada commit Update
})->name('produk.update');

Route::delete('/produk/{id}', function (int $id) {
    // Akan diimplementasikan pada commit Delete
})->name('produk.destroy');
