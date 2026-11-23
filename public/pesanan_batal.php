<?php
// Pembeli membatalkan pesanannya sendiri selama belum menekan konfirmasi pembayaran.
// Setelah itu pembatalan hanya bisa lewat admin, karena uang mungkin sudah dikirim.
require_once __DIR__ . '/../includes/awal.php';
wajib_login();
wajib_post();

$kode = trim($_POST['kode_pesanan'] ?? '');
$pesanan = db_satu('SELECT status FROM membeli WHERE kode_pesanan = ? AND id_pembeli = ?', 'si', [$kode, id_pengguna()]);

if (!$pesanan) {
    set_pesan('gagal', 'Pesanan tidak ditemukan.');
    arahkan(BASE_URL . 'public/pesanan_saya.php');
}
if ($pesanan['status'] !== 'menunggu_bayar') {
    set_pesan('gagal', 'Pesanan ini sudah dikonfirmasi. Hubungi toko lewat WhatsApp kalau mau membatalkan.');
    arahkan(BASE_URL . 'public/pembayaran.php?kode=' . urlencode($kode));
}

if (batalkan_pesanan($kode)) {
    set_pesan('sukses', 'Pesanan dibatalkan dan stok dikembalikan.');
} else {
    set_pesan('gagal', 'Pesanan tidak bisa dibatalkan.');
}
arahkan(BASE_URL . 'public/pembayaran.php?kode=' . urlencode($kode));
