<?php
require_once __DIR__ . '/../includes/awal.php';

$cari = trim($_GET['q'] ?? '');
$sql = "SELECT p.id_produk, p.nama_produk, p.merk, p.berat_gram, p.harga, p.stok, p.tgl_kadaluarsa, p.gambar, p.status, p.kategori AS nama_kategori
        FROM produk p";
$tipe = '';
$param = [];
if ($cari !== '') {
    $sql .= ' WHERE p.nama_produk LIKE ? OR p.merk LIKE ? OR p.kategori LIKE ?';
    $tipe = 'sss';
    $param = ['%' . $cari . '%', '%' . $cari . '%', '%' . $cari . '%'];
}
$sql .= ' ORDER BY p.nama_produk';
$daftar = db_semua($sql, $tipe, $param);

$judul = 'Produk';
$menu_aktif = 'produk';
require __DIR__ . '/../includes/admin_header.php';
?>
<div class="judul-halaman">
    <h1>Produk</h1>
    <a class="btn btn-aksen" href="<?= BASE_URL ?>admin/produk_form.php">Tambah Produk</a>
</div>

<form method="get" class="row g-2 mb-3" role="search">
    <div class="col-sm-6 col-md-4">
        <label for="q" class="visually-hidden">Cari produk</label>
        <input type="search" class="form-control" id="q" name="q" value="<?= e($cari) ?>" placeholder="Cari nama, merk, atau kategori">
    </div>
    <div class="col-auto"><button type="submit" class="btn btn-biru">Cari</button></div>
    <?php if ($cari !== ''): ?><div class="col-auto"><a class="btn btn-garis" href="produk.php">Reset</a></div><?php endif; ?>
</form>

<section class="panel">
    <div class="table-responsive">
        <table class="table">
            <caption class="visually-hidden">Daftar produk</caption>
            <thead><tr><th scope="col">Foto</th><th scope="col">Produk</th><th scope="col">Kategori</th><th scope="col" class="angka">Harga</th><th scope="col" class="angka">Stok</th><th scope="col">Kedaluwarsa</th><th scope="col">Status</th><th scope="col"><span class="visually-hidden">Aksi</span></th></tr></thead>
            <tbody>
            <?php if (!$daftar): ?><tr><td colspan="8" class="text-center text-muted py-4">Produk tidak ditemukan.</td></tr><?php endif; ?>
            <?php foreach ($daftar as $p): ?>
                <tr>
                    <td><img class="foto-tabel" src="<?= e(foto_produk($p['gambar'])) ?>" alt="" width="48" height="48"></td>
                    <td><strong><?= e($p['nama_produk']) ?></strong><br><small class="text-muted"><?= e($p['merk']) ?>, <?= e(teks_berat($p['berat_gram'])) ?></small></td>
                    <td><?= e($p['nama_kategori']) ?></td>
                    <td class="angka"><?= rupiah($p['harga']) ?></td>
                    <td class="angka"><?= (int) $p['stok'] ?></td>
                    <td><?= $p['tgl_kadaluarsa'] ? e(tanggal_indo($p['tgl_kadaluarsa'])) : '-' ?></td>
                    <td><?= $p['status'] === 'aktif' ? 'Aktif' : 'Nonaktif' ?></td>
                    <td>
                        <div class="aksi-tabel">
                            <a class="btn btn-garis btn-sm" href="produk_form.php?id=<?= (int) $p['id_produk'] ?>">Ubah<span class="visually-hidden"> <?= e($p['nama_produk']) ?></span></a>
                            <form method="post" action="produk_hapus.php" data-konfirmasi="Hapus produk <?= e($p['nama_produk']) ?>? Produk yang sudah pernah dipesan atau dibeli hanya dinonaktifkan.">
                                <?= csrf_input() ?>
                                <input type="hidden" name="id_produk" value="<?= (int) $p['id_produk'] ?>">
                                <button type="submit" class="btn btn-bahaya btn-sm">Hapus<span class="visually-hidden"> <?= e($p['nama_produk']) ?></span></button>
                            </form>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
<?php require __DIR__ . '/../includes/admin_footer.php'; ?>
