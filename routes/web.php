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
// PERTEMUAN 6 - FUNGSI READ MAHASISWA DAN PROGRAM STUDI
// ==================================================

Route::get('/mahasiswa', function () {
    try {
        $pdo = DB::connection()->getPdo();

        // Membaca seluruh data mahasiswa
        $pernyataanMahasiswa = $pdo->prepare(
            'SELECT m.nim, m.nama, m.email, m.usia,
                    p.nama_prodi
             FROM mahasiswa AS m
             JOIN program_studi AS p
               ON p.id = m.program_studi_id
             ORDER BY m.nim'
        );
        $pernyataanMahasiswa->execute();
        $daftarMahasiswa = $pernyataanMahasiswa->fetchAll(\PDO::FETCH_ASSOC);

        // Membaca daftar program studi untuk form
        $pernyataanProdi = $pdo->prepare(
            'SELECT id, nama_prodi
             FROM program_studi
             ORDER BY nama_prodi'
        );
        $pernyataanProdi->execute();
        $daftarProgramStudi = $pernyataanProdi->fetchAll(\PDO::FETCH_ASSOC);

        return view('mahasiswa', compact('daftarMahasiswa', 'daftarProgramStudi'));

    } catch (\Throwable $error) {
        report($error);
        return response('Data tidak dapat dibaca. Periksa koneksi dan query.', 500);
    }
})->name('mahasiswa.index');

// ==================================================
// PERTEMUAN 6 - FUNGSI CREATE / TAMBAH MAHASISWA
// ==================================================

Route::post('/mahasiswa', function (Request $request) {

    // Validasi input dari form
    $data = $request->validate([
        'nim' => ['required', 'string', 'max:20'],
        'nama' => ['required', 'string', 'max:100'],
        'email' => ['required', 'email', 'max:100'],
        'usia' => ['required', 'integer', 'min:15', 'max:100'],
        'program_studi_id' => [
            'required',
            'integer',
            'exists:program_studi,id'
        ],
    ], [
        'nim.required' => 'NIM wajib diisi.',
        'nama.required' => 'Nama wajib diisi.',
        'email.required' => 'Email wajib diisi.',
        'email.email' => 'Format email tidak valid.',
        'usia.required' => 'Usia wajib diisi.',
        'usia.integer' => 'Usia harus berupa angka.',
        'program_studi_id.required' =>
            'Program studi wajib dipilih.',
        'program_studi_id.exists' =>
            'Program studi tidak ditemukan.',
    ]);

    try {
        $pdo = DB::connection()->getPdo();

        // Memeriksa apakah NIM atau email sudah digunakan
        $cekDuplikat = $pdo->prepare(
            'SELECT COUNT(*)
             FROM mahasiswa
             WHERE nim = :nim OR email = :email'
        );

        $cekDuplikat->execute([
            'nim' => $data['nim'],
            'email' => $data['email'],
        ]);

        if ((int) $cekDuplikat->fetchColumn() > 0) {
            return back()
                ->withInput()
                ->with(
                    'gagal',
                    'NIM atau email sudah digunakan.'
                );
        }

        // Menyimpan data baru menggunakan prepared statement
        $simpan = $pdo->prepare(
            'INSERT INTO mahasiswa
             (nim, nama, email, usia, program_studi_id)
             VALUES
             (:nim, :nama, :email, :usia, :program_studi_id)'
        );

        $simpan->execute([
            'nim' => $data['nim'],
            'nama' => $data['nama'],
            'email' => $data['email'],
            'usia' => $data['usia'],
            'program_studi_id' => $data['program_studi_id'],
        ]);

        return redirect()
            ->route('mahasiswa.index')
            ->with(
                'berhasil',
                'Data mahasiswa berhasil ditambahkan.'
            );

    } catch (\Throwable $error) {
        report($error);

        return back()
            ->withInput()
            ->with(
                'gagal',
                'Data gagal disimpan. Periksa koneksi dan data.'
            );
    }
})->name('mahasiswa.store');