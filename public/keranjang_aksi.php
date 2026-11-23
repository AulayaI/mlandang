<?php
// Menerima perintah keranjang: tambah, ubah jumlah, hapus.
// Keranjang disimpan di session (lihat includes/keranjang.php).
// Ada dua cara memanggil file ini:
//   1. Dari JavaScript (AJAX): halaman tidak dimuat ulang, jawabannya berupa JSON.
//   2. Dari form biasa (misalnya JavaScript mati): selesai lalu kembali ke halaman asal.
require_once __DIR__ . '/../includes/awal.php';

$lewat_js = ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest';
$halaman_keranjang = BASE_URL . 'public/keranjang.php';

// Mengirim jawaban JSON lalu berhenti.
function jawab_json($data, $kode_http = 200)
{
    http_response_code($kode_http);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data);
    exit;
}

// Satu pintu untuk hasil: JSON untuk JavaScript, pesan flash + pindah halaman untuk form biasa.
function selesai($ok, $pesan, $tujuan, $tambahan = [])
{
    global $lewat_js;
    if ($lewat_js) {
        jawab_json(array_merge([
            'ok' => $ok,
            'pesan' => $pesan,
            'jumlah_keranjang' => jumlah_keranjang(),
        ], $tambahan));
    }
    set_pesan($ok ? 'sukses' : 'gagal', $pesan);
    arahkan($tujuan);
}

if (!sudah_login()) {
    if ($lewat_js) {
        jawab_json(['ok' => false, 'pesan' => 'Masuk dulu untuk berbelanja.',
                    'login' => BASE_URL . 'login-page/login.php'], 401);
    }
    wajib_login();
}
wajib_post();

$aksi = $_POST['aksi'] ?? '';
$id_produk = (int) ($_POST['id_produk'] ?? 0);
$kembali = url_aman($_POST['kembali'] ?? '', $halaman_keranjang);
$keranjang = keranjang_ambil();

if ($aksi === 'hapus') {
    unset($keranjang[$id_produk]);
    keranjang_simpan($keranjang);
    selesai(true, 'Produk dihapus dari keranjang.', $halaman_keranjang);
}

$produk = db_satu("SELECT id_produk, nama_produk, harga, stok FROM produk WHERE id_produk = ? AND status = 'aktif'", 'i', [$id_produk]);
if (!$produk) {
    unset($keranjang[$id_produk]);
    keranjang_simpan($keranjang);
    selesai(false, 'Produk itu sudah tidak dijual.', $halaman_keranjang);
}

$jumlah = (int) ($_POST['jumlah'] ?? 1);
$jumlah = max(1, min($jumlah, 999));

if ($aksi === 'tambah') {
    $baru = ($keranjang[$id_produk] ?? 0) + $jumlah;
} elseif ($aksi === 'ubah') {
    $baru = $jumlah;
} else {
    arahkan($halaman_keranjang);
}

// Jumlah tidak boleh melebihi stok. Dipotong dan pembeli diberi tahu, bukan ditolak diam-diam.
$terpotong = false;
if ($baru > (int) $produk['stok']) {
    $baru = (int) $produk['stok'];
    $terpotong = true;
}
if ($baru < 1) {
    selesai(false, 'Stok ' . $produk['nama_produk'] . ' sedang habis.', $kembali);
}

$keranjang[$id_produk] = $baru;
keranjang_simpan($keranjang);

// Data ringkasan supaya halaman keranjang bisa memperbarui angka tanpa dimuat ulang.
$total = 0;
foreach (isi_keranjang() as $b) {
    $total += $b['harga'] * $b['jumlah'];
}
$tambahan = [
    'jumlah' => $baru,
    'subtotal' => rupiah($produk['harga'] * $baru),
    'total' => rupiah($total),
    'jenis' => count($keranjang),
    'barang' => jumlah_keranjang(),
    'stok' => (int) $produk['stok'],
];

if ($terpotong) {
    $pesan = 'Jumlah ' . $produk['nama_produk'] . ' disesuaikan dengan stok yang tersedia (' . $baru . ').';
    selesai(false, $pesan, $aksi === 'tambah' ? $kembali : $halaman_keranjang, $tambahan);
}
if ($aksi === 'tambah') {
    selesai(true, $produk['nama_produk'] . ' berhasil masuk ke keranjang.', $kembali, $tambahan);
}
selesai(true, 'Jumlah produk diperbarui.', $halaman_keranjang, $tambahan);
