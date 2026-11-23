<?php
// Detail satu produk. Bisa dilihat tanpa login; menambah ke keranjang butuh login.
require_once __DIR__ . '/../includes/awal.php';

$id = (int) ($_GET['id'] ?? 0);
$p = db_satu(
    "SELECT p.*, p.kategori AS nama_kategori
     FROM produk p
     WHERE p.id_produk = ? AND p.status = 'aktif'",
    'i',
    [$id]
);

$menu_aktif = 'produk';

if (!$p) {
    http_response_code(404);
    $judul = 'Produk tidak ditemukan';
    require __DIR__ . '/../includes/header.php';
    echo '<div class="container seksi"><div class="kosong"><p class="mb-2">Produk yang kamu cari tidak ditemukan atau sudah tidak dijual.</p>'
        . '<a href="' . BASE_URL . 'public/produk.php">Kembali ke daftar produk</a></div></div>';
    require __DIR__ . '/../includes/footer.php';
    exit;
}

$terkait = db_semua(
    "SELECT p.*, p.kategori AS nama_kategori
     FROM produk p
     WHERE p.kategori = ? AND p.id_produk <> ? AND p.status = 'aktif'
     ORDER BY p.id_produk DESC LIMIT 4",
    'si',
    [$p['kategori'], $p['id_produk']]
);

$judul = $p['nama_produk'];
require __DIR__ . '/../includes/header.php';
?>
<div class="container seksi-kecil">
    <p class="remah">
        <a href="<?= BASE_URL ?>public/index.php">Beranda</a> /
        <a href="<?= BASE_URL ?>public/produk.php">Produk</a> /
        <a href="<?= BASE_URL ?>public/produk.php?kategori=<?= urlencode($p['kategori']) ?>"><?= e($p['nama_kategori']) ?></a>
    </p>
    <?php tampil_pesan(); ?>

    <div class="row g-4 g-lg-5">
        <div class="col-md-5">
            <div class="foto-detail">
                <img src="<?= e(foto_produk($p['gambar'])) ?>" alt="<?= e($p['nama_produk']) ?>" width="600" height="600">
            </div>
        </div>
        <div class="col-md-7">
            <p class="kategori-kecil"><?= e($p['nama_kategori']) ?></p>
            <h1 class="h2"><?= e($p['nama_produk']) ?></h1>
            <p class="harga-besar"><?= rupiah($p['harga']) ?></p>

            <dl class="data-produk">
                <dt>Merk</dt><dd><?= e($p['merk']) ?></dd>
                <dt>Berat</dt><dd><?= e(teks_berat($p['berat_gram'])) ?></dd>
                <dt>Stok</dt>
                <dd>
                    <?php if ((int) $p['stok'] <= 0): ?>Habis
                    <?php elseif ((int) $p['stok'] <= BATAS_STOK_MENIPIS): ?>Tinggal <?= (int) $p['stok'] ?>
                    <?php else: ?>Tersedia (<?= (int) $p['stok'] ?>)
                    <?php endif; ?>
                </dd>
                <?php if (!empty($p['tgl_kadaluarsa'])): ?>
                    <dt>Kedaluwarsa</dt><dd><?= e(tanggal_indo($p['tgl_kadaluarsa'])) ?></dd>
                <?php endif; ?>
            </dl>

            <?php if ((int) $p['stok'] <= 0): ?>
                <button type="button" class="btn btn-secondary" disabled>Stok Habis</button>
            <?php elseif (sudah_login()): ?>
                <form method="post" action="<?= BASE_URL ?>public/keranjang_aksi.php" class="d-flex flex-wrap gap-2 align-items-center">
                    <?= csrf_input() ?>
                    <input type="hidden" name="aksi" value="tambah">
                    <input type="hidden" name="id_produk" value="<?= (int) $p['id_produk'] ?>">
                    <input type="hidden" name="kembali" value="<?= e($_SERVER['REQUEST_URI']) ?>">
                    <div class="stepper">
                        <button type="button" data-langkah="-1" aria-label="Kurangi jumlah">-</button>
                        <input type="number" name="jumlah" value="1" min="1" max="<?= (int) $p['stok'] ?>" aria-label="Jumlah" inputmode="numeric">
                        <button type="button" data-langkah="1" aria-label="Tambah jumlah">+</button>
                    </div>
                    <button type="submit" class="btn btn-aksen">Tambah ke Keranjang</button>
                </form>
            <?php else: ?>
                <p class="mb-2">Masuk ke akunmu untuk memasukkan produk ini ke keranjang.</p>
                <a class="btn btn-biru" href="<?= BASE_URL ?>login-page/login.php?lanjut=<?= urlencode($_SERVER['REQUEST_URI']) ?>">Masuk untuk Memesan</a>
                <a class="btn btn-garis ms-2" href="<?= BASE_URL ?>login-page/register.php?lanjut=<?= urlencode($_SERVER['REQUEST_URI']) ?>">Daftar Akun</a>
            <?php endif; ?>

            <?php if (!empty($p['deskripsi'])): ?>
                <h2 class="h5 mt-4">Deskripsi</h2>
                <p><?= nl2br(e($p['deskripsi'])) ?></p>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($terkait): ?>
        <h2 class="judul-seksi mt-5"><?= ikon_kristal() ?> Produk sejenis</h2>
        <div class="row g-3 g-lg-4 row-cols-2 row-cols-md-4">
            <?php foreach ($terkait as $t): ?>
                <div class="col"><?php kartu_produk($t); ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
