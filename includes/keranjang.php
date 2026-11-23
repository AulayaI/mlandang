<?php
// Keranjang belanja disimpan di session PHP, bukan di database.
// Bentuknya [id_produk => jumlah]. Keranjang hanya sementara: baru jadi data
// permanen saat checkout, ketika isinya ditulis ke tabel membeli.

function keranjang_ambil()
{
    return $_SESSION['keranjang'] ?? [];
}

function keranjang_simpan($isi)
{
    $_SESSION['keranjang'] = $isi;
}

function jumlah_keranjang()
{
    return (int) array_sum(keranjang_ambil());
}

// Isi keranjang lengkap dengan data produk dari database (harga, stok, nama).
// Harga selalu diambil dari database, bukan dari session, supaya selalu terbaru.
function isi_keranjang()
{
    $keranjang = keranjang_ambil();
    if (!$keranjang) {
        return [];
    }
    $ids = array_keys($keranjang);
    $tanda = implode(',', array_fill(0, count($ids), '?'));
    $baris = db_semua(
        "SELECT id_produk, nama_produk, merk, berat_gram, harga, stok, gambar, status
         FROM produk WHERE id_produk IN ($tanda)",
        str_repeat('i', count($ids)),
        $ids
    );
    $per_id = [];
    foreach ($baris as $b) {
        $per_id[$b['id_produk']] = $b;
    }
    $hasil = [];
    foreach ($keranjang as $id => $jumlah) {
        if (isset($per_id[$id])) {
            $b = $per_id[$id];
            $b['jumlah'] = (int) $jumlah;
            $hasil[] = $b;
        }
    }
    return $hasil;
}
