<?php
require_once __DIR__ . '/../includes/awal.php';
wajib_login();

// Baris membeli dikelompokkan per kode_pesanan supaya satu checkout tampil sebagai satu pesanan.
$pesanan = db_semua(
    'SELECT kode_pesanan, MIN(tgl_pesanan) AS tgl_pesanan, SUM(jumlah * harga_satuan) AS total_bayar,
            SUM(jumlah) AS jumlah_barang, MIN(status) AS status, MAX(id_membeli) AS urutan
     FROM membeli
     WHERE id_pembeli = ?
     GROUP BY kode_pesanan
     ORDER BY urutan DESC',
    'i',
    [id_pengguna()]
);

$judul = 'Pesanan Saya';
$menu_aktif = '';
require __DIR__ . '/../includes/header.php';
?>
<div class="kepala-halaman">
    <div class="container"><h1>Pesanan saya</h1></div>
</div>

<div class="container seksi-kecil">
    <?php tampil_pesan(); ?>
    <?php if (!$pesanan): ?>
        <div class="kosong">
            <p class="mb-2">Kamu belum punya pesanan.</p>
            <a class="btn btn-biru" href="<?= BASE_URL ?>public/produk.php">Lihat Produk</a>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table">
                <caption class="visually-hidden">Daftar pesanan</caption>
                <thead>
                    <tr><th scope="col">No. pesanan</th><th scope="col">Tanggal</th><th scope="col">Barang</th><th scope="col" class="angka">Total</th><th scope="col">Status</th><th scope="col"><span class="visually-hidden">Aksi</span></th></tr>
                </thead>
                <tbody>
                <?php foreach ($pesanan as $p): ?>
                    <tr>
                        <td><?= e($p['kode_pesanan']) ?></td>
                        <td><?= e(tanggal_indo($p['tgl_pesanan'], true)) ?></td>
                        <td><?= (int) $p['jumlah_barang'] ?></td>
                        <td class="angka"><?= rupiah($p['total_bayar']) ?></td>
                        <td><?= label_status($p['status']) ?></td>
                        <td><a class="btn btn-garis btn-sm" href="<?= BASE_URL ?>public/pembayaran.php?kode=<?= urlencode($p['kode_pesanan']) ?>">Lihat<span class="visually-hidden"> pesanan <?= e($p['kode_pesanan']) ?></span></a></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
