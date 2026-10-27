<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ubah Data Produk</title>
</head>
<body>
    <h1>Ubah Data Produk</h1>

    {{-- Feedback Gagal / Validasi --}}
    @if (session('gagal'))
        <p>{{ session('gagal') }}</p>
    @endif
    @if ($errors->any())
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    @endif

    <form action="{{ route('produk.update', $produk['id']) }}" method="POST">
        @csrf
        @method('PUT')
        <p>Kode Produk (tepat 10 karakter alfanumerik)<br>
            <input type="text" name="kode_produk" value="{{ old('kode_produk', $produk['kode_produk']) }}">
        </p>
        <p>Nama Produk (3–100 karakter)<br>
            <input type="text" name="nama_produk" value="{{ old('nama_produk', $produk['nama_produk']) }}">
        </p>
        <p>SKU (5–30 karakter)<br>
            <input type="text" name="sku" value="{{ old('sku', $produk['sku']) }}">
        </p>
        <p>Stok (bilangan bulat 0–500)<br>
            <input type="number" name="stok" value="{{ old('stok', $produk['stok']) }}">
        </p>
        <p>Kategori Produk<br>
            <select name="kategori_produk_id">
                @foreach ($daftarKategori as $kategori)
                    <option value="{{ $kategori['id'] }}"
                        @selected(old('kategori_produk_id', $produk['kategori_produk_id']) == $kategori['id'])>
                        {{ $kategori['nama_kategori'] }}
                    </option>
                @endforeach
            </select>
        </p>
        <button type="submit">Simpan Perubahan</button>
        <a href="{{ route('produk.index') }}">Kembali</a>
    </form>
</body>
</html>
