<?php
// Produk yang sudah tercatat di pesanan atau transaksi tidak boleh dihapus,
// karena riwayatnya masih membutuhkan data produk itu. Produk seperti itu dinonaktifkan.
require_once __DIR__ . '/../includes/awal.php';
wajib_admin();
wajib_post();

$id = (int) ($_POST['id_produk'] ?? 0);
$p = db_satu('SELECT nama_produk, gambar FROM produk WHERE id_produk = ?', 'i', [$id]);
if (!$p) {
    set_pesan('gagal', 'Produk tidak ditemukan.');
    arahkan(BASE_URL . 'admin/produk.php');
}

$dipakai = (int) db_satu('SELECT (SELECT COUNT(*) FROM membeli WHERE id_produk = ?) + (SELECT COUNT(*) FROM transaksi WHERE id_produk = ?) AS n', 'ii', [$id, $id])['n'];
if ($dipakai > 0) {
    db_jalankan("UPDATE produk SET status = 'nonaktif' WHERE id_produk = ?", 'i', [$id]);
    set_pesan('sukses', $p['nama_produk'] . ' sudah punya riwayat, jadi dinonaktifkan (tidak tampil di toko) dan tidak dihapus.');
} else {
    db_jalankan('DELETE FROM produk WHERE id_produk = ?', 'i', [$id]);
    hapus_gambar_produk($p['gambar']);
    set_pesan('sukses', 'Produk dihapus.');
}
arahkan(BASE_URL . 'admin/produk.php');
