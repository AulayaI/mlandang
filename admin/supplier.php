<?php
require_once __DIR__ . '/../includes/awal.php';
wajib_admin();

$galat = [];
$ubah = (int) ($_GET['ubah'] ?? 0);
$nilai = ['nama_supplier' => '', 'no_telp' => '', 'alamat' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    wajib_post();
    if (($_POST['aksi'] ?? '') === 'hapus') {
        $ids = (int) ($_POST['id_supplier'] ?? 0);
        $dipakai = (int) db_satu('SELECT COUNT(*) AS n FROM transaksi WHERE id_supplier = ?', 'i', [$ids])['n'];
        if ($dipakai > 0) {
            set_pesan('gagal', 'Supplier sudah punya riwayat transaksi, jadi tidak bisa dihapus.');
        } else {
            db_jalankan('DELETE FROM supplier WHERE id_supplier = ?', 'i', [$ids]);
            set_pesan('sukses', 'Supplier dihapus.');
        }
        arahkan(BASE_URL . 'admin/supplier.php');
    }
    $ubah = (int) ($_POST['id_supplier'] ?? 0);
    foreach ($nilai as $k => $v) {
        $nilai[$k] = trim($_POST[$k] ?? '');
    }
    if ($nilai['nama_supplier'] === '' || mb_strlen($nilai['nama_supplier']) > 100) {
        $galat['nama_supplier'] = 'Nama supplier wajib diisi, maksimal 100 karakter.';
    }
    if (!nomor_wa_valid($nilai['no_telp'])) {
        $galat['no_telp'] = 'Nomor telepon berupa angka, contoh: 081234567890.';
    }
    if ($nilai['alamat'] === '' || mb_strlen($nilai['alamat']) > 255) {
        $galat['alamat'] = 'Alamat wajib diisi, maksimal 255 karakter.';
    }
    if (!$galat) {
        if ($ubah) {
            db_jalankan('UPDATE supplier SET nama_supplier=?, no_telp=?, alamat=? WHERE id_supplier=?', 'sssi', [$nilai['nama_supplier'], $nilai['no_telp'], $nilai['alamat'], $ubah]);
            set_pesan('sukses', 'Supplier diperbarui.');
        } else {
            db_jalankan('INSERT INTO supplier (nama_supplier,no_telp,alamat) VALUES (?,?,?)', 'sss', [$nilai['nama_supplier'], $nilai['no_telp'], $nilai['alamat']]);
            set_pesan('sukses', 'Supplier ditambahkan.');
        }
        arahkan(BASE_URL . 'admin/supplier.php');
    }
} elseif ($ubah) {
    $nilai = db_satu('SELECT nama_supplier, no_telp, alamat FROM supplier WHERE id_supplier = ?', 'i', [$ubah]) ?: $nilai;
}

$daftar = db_semua('SELECT * FROM supplier ORDER BY nama_supplier');
$judul = 'Supplier';
$menu_aktif = 'supplier';
require __DIR__ . '/../includes/admin_header.php';
?>
<div class="judul-halaman"><h1>Supplier</h1></div>
<div class="row g-3">
    <div class="col-lg-4">
        <section class="panel">
            <h2><?= $ubah ? 'Ubah supplier' : 'Tambah supplier' ?></h2>
            <form method="post" action="" data-validasi>
                <?= csrf_input() ?>
                <input type="hidden" name="id_supplier" value="<?= $ubah ?>">
                <?php field('nama_supplier', 'Nama supplier', 'text', $nilai['nama_supplier'], $galat, 'data-wajib maxlength="100"'); ?>
                <?php field('no_telp', 'No. telepon', 'tel', $nilai['no_telp'], $galat, 'data-wajib data-tipe="telepon" maxlength="20"'); ?>
                <?php field_area('alamat', 'Alamat', $nilai['alamat'], $galat, 'data-wajib maxlength="255"', 2); ?>
                <button type="submit" class="btn btn-aksen">Simpan</button>
                <?php if ($ubah): ?><a class="btn btn-garis" href="supplier.php">Batal</a><?php endif; ?>
            </form>
        </section>
    </div>
    <div class="col-lg-8">
        <section class="panel">
            <div class="table-responsive">
                <table class="table">
                    <caption class="visually-hidden">Daftar supplier</caption>
                    <thead><tr><th scope="col">Supplier</th><th scope="col">Telepon</th><th scope="col">Alamat</th><th scope="col"><span class="visually-hidden">Aksi</span></th></tr></thead>
                    <tbody>
                    <?php foreach ($daftar as $s): ?>
                        <tr>
                            <td><?= e($s['nama_supplier']) ?></td><td><?= e($s['no_telp']) ?></td><td><?= e($s['alamat']) ?></td>
                            <td><div class="aksi-tabel">
                                <a class="btn btn-garis btn-sm" href="supplier.php?ubah=<?= (int) $s['id_supplier'] ?>">Ubah<span class="visually-hidden"> <?= e($s['nama_supplier']) ?></span></a>
                                <form method="post" data-konfirmasi="Hapus supplier <?= e($s['nama_supplier']) ?>?">
                                    <?= csrf_input() ?><input type="hidden" name="aksi" value="hapus"><input type="hidden" name="id_supplier" value="<?= (int) $s['id_supplier'] ?>">
                                    <button type="submit" class="btn btn-bahaya btn-sm">Hapus<span class="visually-hidden"> <?= e($s['nama_supplier']) ?></span></button>
                                </form>
                            </div></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</div>
<?php require __DIR__ . '/../includes/admin_footer.php'; ?>
