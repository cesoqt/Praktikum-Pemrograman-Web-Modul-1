<?php

use Illuminate\Support\Facades\Route;

Route::get('/latihan-php', function () {
    $nama = 'Muhammad Al-Fikry Akbar';
    $nilai = [60, 75, 55, 70, 80];

    $hitungRataRata = function (array $data): float {
        $total = 0;
        foreach ($data as $angka) {
            $total += $angka;
        }
        return $total / count($data);
    };

    $rataRata = $hitungRataRata($nilai);
    if ($rataRata >= 75) {
        $status = 'Lulus';
    } else {
        $status = 'Perlu Perbaikan';
    }

    return view('latihan-php', compact(
        'nama', 'nilai', 'rataRata', 'status'
    ));
});



use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

Route::get('/form-mahasiswa', function () {
    return view('form-mahasiswa');
});

Route::post('/form-mahasiswa', function (Request $request) {

    $dataBersih = [
        'nama' => strip_tags(trim((string) $request->input('nama'))),

        'NIM' => trim((string) $request->input('NIM')),

        'email' => filter_var(
            (string) $request->input('email'),
            FILTER_SANITIZE_EMAIL
        ),

        'usia' => trim((string) $request->input('usia')),
    ];

$validator = Validator::make($dataBersih, [
    'nama' => ['required', 'min:3', 'max:50'],

    'NIM' => ['required', 'digits_between:8,12'],

    'email' => ['required', 'email'],

    'usia' => ['required', 'integer', 'min:17', 'max:60'],
], [
    'nama.required' => 'Nama wajib diisi.',
    'nama.min' => 'Nama minimal 3 karakter.',
    'nama.max' => 'Nama maksimal 50 karakter.',

    'NIM.required' => 'NIM wajib diisi.',
    'NIM.digits_between' => 'NIM harus berisi 8–12 digit.',

    'email.required' => 'Email wajib diisi.',
    'email.email' => 'Format email tidak valid.',

    'usia.required' => 'Usia wajib diisi.',
    'usia.integer' => 'Usia harus berupa angka.',
    'usia.min' => 'Usia minimal 17 tahun.',
    'usia.max' => 'Usia maksimal 60 tahun.',
]);


    if ($validator->fails()) {
        return redirect('/form-mahasiswa')
            ->withErrors($validator)
            ->withInput();
    }

    $data = $validator->validated();

    $data['usia'] = (int) $data['usia'];

    return view('hasil-form', ['data' => $data]);
});
