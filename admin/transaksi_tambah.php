<?php
// Catat transaksi stok dari supplier. Stok produk bertambah saat transaksi disimpan.
require_once __DIR__ . '/../includes/awal.php';
wajib_admin();

$supplier = [];
foreach (db_semua('SELECT id_supplier, nama_supplier FROM supplier ORDER BY nama_supplier') as $s) {
    $supplier[$s['id_supplier']] = $s['nama_supplier'];
}
$produk = [];
foreach (db_semua('SELECT id_produk, nama_produk, merk FROM produk ORDER BY nama_produk') as $p) {
    $produk[$p['id_produk']] = $p['nama_produk'] . ' (' . $p['merk'] . ')';
}
if (!$supplier || !$produk) {
    set_pesan('gagal', 'Isi data supplier dan produk dulu sebelum mencatat transaksi.');
    arahkan(BASE_URL . 'admin/transaksi.php');
}

$id_supplier = key($supplier);
$baris_isi = [['produk' => '', 'jumlah' => '', 'harga' => '']];
$galat = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    wajib_post();
    $id_supplier = (int) ($_POST['id_supplier'] ?? 0);
    $p_produk = (array) ($_POST['produk'] ?? []);
    $p_jumlah = (array) ($_POST['jumlah'] ?? []);
    $p_harga = (array) ($_POST['harga'] ?? []);

    $baris_isi = [];
    $bersih = [];
    foreach ($p_produk as $i => $idp) {
        $j = trim((string) ($p_jumlah[$i] ?? ''));
        $h = trim((string) ($p_harga[$i] ?? ''));
        $baris_isi[] = ['produk' => $idp, 'jumlah' => $j, 'harga' => $h];
        if (!isset($produk[(int) $idp]) || !ctype_digit($j) || (int) $j < 1 || !ctype_digit($h) || (int) $h < 1) {
            $galat = 'Setiap baris harus berisi produk, jumlah (angka, minimal 1), dan harga beli (angka rupiah tanpa titik).';
            break;
        }
        $bersih[] = [(int) $idp, (int) $j, (int) $h];
    }
    if (!isset($supplier[$id_supplier])) {
        $galat = 'Pilih supplier.';
    }
    if ($galat === '' && !$bersih) {
        $galat = 'Tambahkan minimal satu produk.';
    }

    if ($galat === '') {
        db_mulai();
        try {
            // Satu baris transaksi untuk tiap produk. Stok produk bertambah otomatis oleh trigger
            // trg_stok_masuk di database (lihat database/frozen_food_web.sql).
            $sekarang = date('Y-m-d H:i:s');
            foreach ($bersih as $b) {
                db_jalankan(
                    'INSERT INTO transaksi (id_supplier, id_produk, jumlah, harga_beli, tgl_transaksi) VALUES (?, ?, ?, ?, ?)',
                    'iiids', [$id_supplier, $b[0], $b[1], $b[2], $sekarang]
                );
            }
            db_simpan();
        } catch (Throwable $e) {
            db_batal();
            throw $e;
        }
        set_pesan('sukses', 'Transaksi dicatat dan stok bertambah.');
        arahkan(BASE_URL . 'admin/transaksi.php');
    }
}

$judul = 'Catat Transaksi Stok';
$menu_aktif = 'transaksi';
require __DIR__ . '/../includes/admin_header.php';
?>
<div class="judul-halaman"><h1>Catat transaksi stok</h1><a class="btn btn-garis" href="transaksi.php">Kembali</a></div>
<section class="panel">
    <?php if ($galat !== ''): ?><div class="alert alert-danger" role="alert"><?= e($galat) ?></div><?php endif; ?>
    <form method="post" action="">
        <?= csrf_input() ?>
        <div class="row"><div class="col-md-6"><?php field_pilih('id_supplier', 'Supplier', $supplier, $id_supplier, []); ?></div></div>

        <h2 class="h5">Barang yang dibeli</h2>
        <div id="daftar-baris">
            <?php foreach ($baris_isi as $b): ?>
                <div class="baris-beli row g-2 mb-2 align-items-end">
                    <div class="col-md-5">
                        <label class="form-label">Produk
                            <select name="produk[]" class="form-select" required>
                                <option value="">Pilih produk</option>
                                <?php foreach ($produk as $idp => $nama): ?>
                                    <option value="<?= (int) $idp ?>"<?= (string) $idp === (string) $b['produk'] ? ' selected' : '' ?>><?= e($nama) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </label>
                    </div>
                    <div class="col-6 col-md-2"><label class="form-label">Jumlah<input type="number" name="jumlah[]" class="form-control isi-jumlah" min="1" required value="<?= e($b['jumlah']) ?>"></label></div>
                    <div class="col-6 col-md-3"><label class="form-label">Harga beli satuan (Rp)<input type="number" name="harga[]" class="form-control isi-harga" min="1" required value="<?= e($b['harga']) ?>"></label></div>
                    <div class="col-12 col-md-2"><button type="button" class="btn btn-garis w-100 hapus-baris">Hapus baris</button></div>
                </div>
            <?php endforeach; ?>
        </div>
        <button type="button" id="tambah-baris" class="btn btn-garis mb-3">Tambah Baris</button>
        <p class="fs-5">Perkiraan total: <strong id="perkiraan-total">Rp 0</strong></p>
        <button type="submit" class="btn btn-aksen">Simpan Transaksi</button>
    </form>
</section>
<?php require __DIR__ . '/../includes/admin_footer.php'; ?>
