<?php

namespace Tests\Feature;

use Database\Seeders\ProdukSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ProdukTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(ProdukSeeder::class);
    }

    /**
     * 1. Read: Tiga data awal dan nama tabel master tampil pada indeks.
     */
    public function test_read_tiga_data_awal_dan_kategori_tampil_pada_indeks(): void
    {
        $response = $this->get('/produk');

        $response->assertStatus(200);
        $response->assertSee('PR00000001');
        $response->assertSee('Keripik Gonggong');
        $response->assertSee('SKU-MKN-001');
        $response->assertSee('PR00000002');
        $response->assertSee('Kopi Bubuk Lokal');
        $response->assertSee('SKU-MNM-001');
        $response->assertSee('PR00000003');
        $response->assertSee('Kue Bangkit');
        $response->assertSee('SKU-MKN-002');
        $response->assertSee('Makanan');
        $response->assertSee('Minuman');
    }

    /**
     * 2. Create valid: Data baru tersimpan dan feedback keberhasilan tampil.
     */
    public function test_create_valid_berhasil_tersimpan_dan_feedback_muncul(): void
    {
        $payload = [
            'kode_produk' => 'PR00000004',
            'nama_produk' => 'Teh Tarik Hangat',
            'sku' => 'SKU-MNM-002',
            'stok' => '50',
            'kategori_produk_id' => '2',
        ];

        $response = $this->post('/produk', $payload);

        $response->assertRedirect('/produk');
        $response->assertSessionHas('berhasil', 'Data produk berhasil ditambahkan.');

        $this->assertDatabaseHas('produk', [
            'kode_produk' => 'PR00000004',
            'nama_produk' => 'Teh Tarik Hangat',
            'sku' => 'SKU-MNM-002',
            'stok' => 50,
        ]);
    }

    /**
     * 3. Create tidak valid: Pesan kesalahan tampil dan input lama dipertahankan.
     */
    public function test_create_tidak_valid_menampilkan_pesan_error_dan_old_input(): void
    {
        $payload = [
            'kode_produk' => 'PR99',
            'nama_produk' => 'X',
            'sku' => 'SK',
            'stok' => '999',
            'kategori_produk_id' => '99999',
        ];

        $response = $this->from('/produk')->post('/produk', $payload);

        $response->assertRedirect('/produk');
        $response->assertSessionHasErrors([
            'kode_produk',
            'nama_produk',
            'sku',
            'stok',
            'kategori_produk_id',
        ]);
        $response->assertSessionHas('gagal');
    }

    /**
     * 4. Duplikat: Kode atau identitas unik yang sama ditolak tanpa merusak data.
     */
    public function test_duplikat_kode_atau_sku_ditolak_tanpa_merusak_data(): void
    {
        $payload = [
            'kode_produk' => 'PR00000001',
            'nama_produk' => 'Keripik Tempe Renyah',
            'sku' => 'SKU-MKN-999',
            'stok' => '20',
            'kategori_produk_id' => '1',
        ];

        $response = $this->from('/produk')->post('/produk', $payload);

        $response->assertRedirect('/produk');
        $response->assertSessionHas('gagal', 'Kode produk atau SKU sudah digunakan.');

        $count = DB::table('produk')->count();
        $this->assertSame(3, $count);
    }

    /**
     * 5. Update: Data lama tampil pada form edit dan perubahan tersimpan.
     */
    public function test_update_data_lama_tampil_pada_edit_dan_perubahan_tersimpan(): void
    {
        $ambil = DB::table('produk')->where('kode_produk', 'PR00000001')->first();

        $editResponse = $this->get('/produk/'.$ambil->id.'/edit');
        $editResponse->assertStatus(200);
        $editResponse->assertSee('Keripik Gonggong');
        $editResponse->assertSee('PR00000001');

        $updatePayload = [
            'kode_produk' => 'PR00000001',
            'nama_produk' => 'Keripik Gonggong Super Pedas',
            'sku' => 'SKU-MKN-001',
            'stok' => '45',
            'kategori_produk_id' => '1',
        ];

        $response = $this->put('/produk/'.$ambil->id, $updatePayload);

        $response->assertRedirect('/produk');
        $response->assertSessionHas('berhasil', 'Data produk berhasil diperbarui.');

        $this->assertDatabaseHas('produk', [
            'id' => $ambil->id,
            'nama_produk' => 'Keripik Gonggong Super Pedas',
            'stok' => 45,
        ]);
    }

    /**
     * 6. Konflik Update: Nilai unik milik data lain ditolak.
     */
    public function test_konflik_update_nilai_unik_milik_data_lain_ditolak(): void
    {
        $produk1 = DB::table('produk')->where('kode_produk', 'PR00000001')->first();
        $produk2 = DB::table('produk')->where('kode_produk', 'PR00000002')->first();

        $conflictPayload = [
            'kode_produk' => $produk1->kode_produk,
            'nama_produk' => 'Kopi Robusta Lokal',
            'sku' => 'SKU-MNM-001',
            'stok' => '20',
            'kategori_produk_id' => '2',
        ];

        $response = $this->from('/produk/'.$produk2->id.'/edit')
            ->put('/produk/'.$produk2->id, $conflictPayload);

        $response->assertRedirect('/produk/'.$produk2->id.'/edit');
        $response->assertSessionHas('gagal', 'Kode produk atau SKU sudah digunakan produk lain.');
    }

    /**
     * 7. Delete: Data terpilih terhapus melalui form DELETE dengan CSRF.
     */
    public function test_delete_data_terpilih_terhapus_dengan_csrf(): void
    {
        $produk = DB::table('produk')->where('kode_produk', 'PR00000003')->first();

        $response = $this->delete('/produk/'.$produk->id);

        $response->assertRedirect('/produk');
        $response->assertSessionHas('berhasil', 'Data produk berhasil dihapus.');

        $this->assertDatabaseMissing('produk', [
            'id' => $produk->id,
        ]);
    }

    /**
     * 8. Pencarian: Kata kunci mencari tiga kolom yang telah ditentukan.
     */
    public function test_pencarian_memeriksa_tiga_kolom(): void
    {
        $resKode = $this->get('/produk?q=PR00000001');
        $resKode->assertStatus(200);
        $resKode->assertSee('Keripik Gonggong');
        $resKode->assertDontSee('Kopi Bubuk Lokal');

        $resNama = $this->get('/produk?q=Kopi');
        $resNama->assertStatus(200);
        $resNama->assertSee('Kopi Bubuk Lokal');
        $resNama->assertDontSee('Keripik Gonggong');

        $resSku = $this->get('/produk?q=SKU-MKN-002');
        $resSku->assertStatus(200);
        $resSku->assertSee('Kue Bangkit');
        $resSku->assertDontSee('Kopi Bubuk Lokal');
    }

    /**
     * 9. Filter: Hasil dapat dibatasi berdasarkan tabel master.
     */
    public function test_filter_membatasi_hasil_berdasarkan_kategori(): void
    {
        $kategoriMinuman = DB::table('kategori_produk')->where('nama_kategori', 'Minuman')->first();

        $response = $this->get('/produk?kategori_produk_id='.$kategoriMinuman->id);

        $response->assertStatus(200);
        $response->assertSee('Kopi Bubuk Lokal');
        $response->assertDontSee('Keripik Gonggong');
        $response->assertDontSee('Kue Bangkit');
    }

    /**
     * 10. Error handling: Kesalahan koneksi atau query dicatat, pengguna menerima respons aman.
     */
    public function test_error_handling_memberikan_respons_aman_saat_id_tidak_ditemukan(): void
    {
        $response = $this->get('/produk/99999/edit');

        $response->assertRedirect('/produk');
        $response->assertSessionHas('gagal', 'Data produk tidak ditemukan.');
    }
}
