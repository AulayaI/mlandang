<?php
// Transaksi supplier - produk: riwayat stok masuk.
require_once __DIR__ . '/../includes/awal.php';
wajib_admin();

$daftar = db_semua(
    'SELECT t.id_transaksi, t.tgl_transaksi, t.jumlah, t.harga_beli, s.nama_supplier, p.nama_produk
     FROM transaksi t
     JOIN supplier s ON s.id_supplier = t.id_supplier
     JOIN produk p ON p.id_produk = t.id_produk
     ORDER BY t.id_transaksi DESC'
);

$judul = 'Transaksi Stok';
$menu_aktif = 'transaksi';
require __DIR__ . '/../includes/admin_header.php';
?>
<div class="judul-halaman">
    <h1>Transaksi stok masuk</h1>
    <a class="btn btn-aksen" href="transaksi_tambah.php">Catat Transaksi</a>
</div>
<section class="panel">
    <div class="table-responsive">
        <table class="table">
            <caption class="visually-hidden">Riwayat stok masuk dari supplier</caption>
            <thead><tr><th scope="col">Tanggal</th><th scope="col">Supplier</th><th scope="col">Produk</th><th scope="col" class="angka">Jumlah</th><th scope="col" class="angka">Harga beli</th><th scope="col" class="angka">Total</th></tr></thead>
            <tbody>
            <?php if (!$daftar): ?><tr><td colspan="6" class="text-center text-muted py-4">Belum ada transaksi.</td></tr><?php endif; ?>
            <?php foreach ($daftar as $t): ?>
                <tr>
                    <td><?= e(tanggal_indo($t['tgl_transaksi'], true)) ?></td>
                    <td><?= e($t['nama_supplier']) ?></td>
                    <td><?= e($t['nama_produk']) ?></td>
                    <td class="angka"><?= (int) $t['jumlah'] ?></td>
                    <td class="angka"><?= rupiah($t['harga_beli']) ?></td>
                    <td class="angka"><?= rupiah($t['jumlah'] * $t['harga_beli']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
<?php require __DIR__ . '/../includes/admin_footer.php'; ?>
