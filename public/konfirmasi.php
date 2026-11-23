<?php
// Pembeli menekan "Sudah Bayar". Status berubah ke menunggu_konfirmasi, lalu pembeli
// dikirim ke WhatsApp toko dengan teks rincian pesanan yang sudah terisi.
// Sistem tidak bisa memastikan uang benar-benar masuk: admin yang memeriksa
// bukti pembayaran di WhatsApp lalu mengubah status pesanan.
require_once __DIR__ . '/../includes/awal.php';
wajib_login();
wajib_post();

$kode = trim($_POST['kode_pesanan'] ?? '');
$data = ambil_pesanan($kode);
$halaman_pesanan = BASE_URL . 'public/pembayaran.php?kode=' . urlencode($kode);

if (!$data || (int) $data['pesanan']['id_pembeli'] !== id_pengguna()) {
    set_pesan('gagal', 'Pesanan tidak ditemukan.');
    arahkan(BASE_URL . 'public/pesanan_saya.php');
}
$pesanan = $data['pesanan'];
if (!in_array($pesanan['status'], ['menunggu_bayar', 'menunggu_konfirmasi'], true)) {
    set_pesan('gagal', 'Pesanan ini sudah tidak menunggu konfirmasi.');
    arahkan($halaman_pesanan);
}

if ($pesanan['status'] === 'menunggu_bayar') {
    db_jalankan("UPDATE membeli SET status = 'menunggu_konfirmasi' WHERE kode_pesanan = ? AND status = 'menunggu_bayar'", 's', [$kode]);
}

arahkan(link_wa(NOMOR_WA_TOKO, teks_konfirmasi_wa($pesanan, $data['item'])));
