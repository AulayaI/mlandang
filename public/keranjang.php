<?php
require_once __DIR__ . '/../includes/awal.php';
wajib_login();

$item = isi_keranjang();

$total = 0;
$ada_masalah = false;
foreach ($item as $i => $b) {
    $item[$i]['subtotal'] = $b['harga'] * $b['jumlah'];
    $total += $item[$i]['subtotal'];
    $item[$i]['masalah'] = '';
    if ($b['status'] !== 'aktif') {
        $item[$i]['masalah'] = 'Produk ini sudah tidak dijual. Hapus dari keranjang.';
    } elseif ((int) $b['stok'] < (int) $b['jumlah']) {
        $item[$i]['masalah'] = 'Stok tinggal ' . (int) $b['stok'] . '. Kurangi jumlahnya.';
    }
    if ($item[$i]['masalah'] !== '') {
        $ada_masalah = true;
    }
}

$judul = 'Keranjang';
$menu_aktif = '';
require __DIR__ . '/../includes/header.php';
?>
<div class="kepala-halaman">
    <div class="container"><h1>Keranjang belanja</h1></div>
</div>

<div class="container seksi-kecil">
    <?php tampil_pesan(); ?>

    <?php if (!$item): ?>
        <div class="kosong">
            <p class="mb-2">Keranjangmu masih kosong.</p>
            <a class="btn btn-biru" href="<?= BASE_URL ?>public/produk.php">Lihat Produk</a>
        </div>
    <?php else: ?>
        <div class="row g-4">
            <div class="col-lg-8">
                <?php foreach ($item as $b): ?>
                    <div class="baris-keranjang" data-baris-keranjang>
                        <div class="foto-kecil"><img src="<?= e(foto_produk($b['gambar'])) ?>" alt="" width="80" height="80"></div>
                        <div class="info">
                            <a href="<?= BASE_URL ?>public/detail.php?id=<?= (int) $b['id_produk'] ?>"><?= e($b['nama_produk']) ?></a>
                            <div class="berat mb-0"><?= e($b['merk']) ?>, <?= e(teks_berat($b['berat_gram'])) ?> | <?= rupiah($b['harga']) ?></div>
                            <?php if ($b['masalah'] !== ''): ?>
                                <div class="text-danger small" role="alert"><?= e($b['masalah']) ?></div>
                            <?php endif; ?>
                        </div>
                        <div class="ubah">
                            <?php if ($b['status'] === 'aktif'): ?>
                                <?php /* Tanpa tombol Ubah: tiap kali + atau - ditekan, main.js mengirim jumlah baru dan harga ikut berubah. */ ?>
                                <form method="post" action="<?= BASE_URL ?>public/keranjang_aksi.php" class="form-jumlah-live">
                                    <?= csrf_input() ?>
                                    <input type="hidden" name="aksi" value="ubah">
                                    <input type="hidden" name="id_produk" value="<?= (int) $b['id_produk'] ?>">
                                    <div class="stepper">
                                        <button type="button" data-langkah="-1" aria-label="Kurangi jumlah <?= e($b['nama_produk']) ?>">-</button>
                                        <input type="number" name="jumlah" value="<?= (int) $b['jumlah'] ?>" min="1" max="<?= max(1, (int) $b['stok']) ?>" aria-label="Jumlah <?= e($b['nama_produk']) ?>" inputmode="numeric">
                                        <button type="button" data-langkah="1" aria-label="Tambah jumlah <?= e($b['nama_produk']) ?>">+</button>
                                    </div>
                                </form>
                            <?php endif; ?>
                            <form method="post" action="<?= BASE_URL ?>public/keranjang_aksi.php" data-konfirmasi="Hapus <?= e($b['nama_produk']) ?> dari keranjang?">
                                <?= csrf_input() ?>
                                <input type="hidden" name="aksi" value="hapus">
                                <input type="hidden" name="id_produk" value="<?= (int) $b['id_produk'] ?>">
                                <button type="submit" class="btn btn-garis btn-sm">Hapus</button>
                            </form>
                        </div>
                        <div class="subtotal" data-subtotal><?= rupiah($b['subtotal']) ?></div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="col-lg-4">
                <div class="ringkasan">
                    <h2 class="h5">Ringkasan</h2>
                    <ul>
                        <li><span data-jenis><?= count($item) ?> jenis produk</span><span data-barang><?= array_sum(array_column($item, 'jumlah')) ?> barang</span></li>
                    </ul>
                    <div class="total"><span>Total</span><span class="angka" data-total><?= rupiah($total) ?></span></div>
                    <?php if ($ada_masalah): ?>
                        <p class="text-danger small mt-3 mb-2" role="alert">Perbaiki produk bertanda merah sebelum checkout.</p>
                        <button type="button" class="btn btn-secondary w-100" disabled>Lanjut ke Checkout</button>
                    <?php else: ?>
                        <a class="btn btn-aksen w-100 mt-3" href="<?= BASE_URL ?>public/checkout.php">Lanjut ke Checkout</a>
                    <?php endif; ?>
                    <a class="btn btn-garis w-100 mt-2" href="<?= BASE_URL ?>public/produk.php">Tambah Produk Lain</a>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
