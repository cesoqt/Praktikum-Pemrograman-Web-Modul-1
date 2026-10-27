<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Data Produk UMKM</title>
</head>
<body>
    <h1>Sistem Data Produk UMKM</h1>

    {{-- Feedback Operasi --}}
    @if (session('berhasil'))
        <p>{{ session('berhasil') }}</p>
    @endif
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

    {{-- Form Tambah Produk --}}
    <h2>Tambah Produk</h2>
    <form action="{{ route('produk.store') }}" method="POST">
        @csrf
        <p>Kode Produk (tepat 10 karakter alfanumerik)<br>
            <input type="text" name="kode_produk" value="{{ old('kode_produk') }}">
        </p>
        <p>Nama Produk (3–100 karakter)<br>
            <input type="text" name="nama_produk" value="{{ old('nama_produk') }}">
        </p>
        <p>SKU (5–30 karakter)<br>
            <input type="text" name="sku" value="{{ old('sku') }}">
        </p>
        <p>Stok (bilangan bulat 0–500)<br>
            <input type="number" name="stok" value="{{ old('stok') }}">
        </p>
        <p>Kategori Produk<br>
            <select name="kategori_produk_id">
                <option value="">-- Pilih Kategori --</option>
                @foreach ($daftarKategori as $kategori)
                    <option value="{{ $kategori['id'] }}"
                        @selected(old('kategori_produk_id') == $kategori['id'])>
                        {{ $kategori['nama_kategori'] }}
                    </option>
                @endforeach
            </select>
        </p>
        <button type="submit">Tambah Produk</button>
    </form>

    {{-- Form Pencarian dan Filter --}}
    <h2>Pencarian dan Filter</h2>
    <form action="{{ route('produk.index') }}" method="GET">
        <p>Kata Kunci (Kode / Nama / SKU)<br>
            <input type="text" name="q" value="{{ $kataKunci ?? '' }}">
        </p>
        <p>Filter Kategori<br>
            <select name="kategori_produk_id">
                <option value="">-- Semua Kategori --</option>
                @foreach ($daftarKategori as $kategori)
                    <option value="{{ $kategori['id'] }}"
                        @selected((string) ($kategoriProdukId ?? '') === (string) $kategori['id'])>
                        {{ $kategori['nama_kategori'] }}
                    </option>
                @endforeach
            </select>
        </p>
        <button type="submit">Cari</button>
        <a href="{{ route('produk.index') }}">Reset</a>
    </form>

    {{-- Daftar Produk --}}
    <h2>Daftar Produk</h2>
    <table border="1" cellpadding="6" cellspacing="0">
        <thead>
            <tr>
                <th>No</th>
                <th>Kode Produk</th>
                <th>Nama Produk</th>
                <th>SKU</th>
                <th>Stok</th>
                <th>Status Stok</th>
                <th>Kategori</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($daftarProduk as $index => $produk)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $produk['kode_produk'] }}</td>
                    <td>{{ $produk['nama_produk'] }}</td>
                    <td>{{ $produk['sku'] }}</td>
                    <td>{{ $produk['stok'] }}</td>
                    <td>
                        @php
                            $status = statusStok((int) $produk['stok']);
                        @endphp
                        @if ($status === 'Tersedia')
                            Tersedia
                        @else
                            Habis
                        @endif
                    </td>
                    <td>{{ $produk['nama_kategori'] }}</td>
                    <td>
                        <a href="{{ route('produk.edit', $produk['id']) }}">Ubah</a>
                        <form action="{{ route('produk.destroy', $produk['id']) }}" method="POST" style="display:inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" onclick="return confirm('Hapus data produk ini?')">Hapus</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8">Data produk tidak ditemukan.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
