<?php
require_once __DIR__ . '/../includes/awal.php';
wajib_admin();

$status = $_GET['status'] ?? '';
$semua_status = ['menunggu_bayar', 'menunggu_konfirmasi', 'diproses', 'selesai', 'batal', 'kedaluwarsa'];
if (!in_array($status, $semua_status, true)) {
    $status = '';
}
$cari = trim($_GET['q'] ?? '');

$syarat = [];
$tipe = '';
$param = [];
if ($status !== '') {
    $syarat[] = 'status = ?';
    $tipe .= 's';
    $param[] = $status;
}
if ($cari !== '') {
    $syarat[] = '(nama_penerima LIKE ? OR email_penerima LIKE ?)';
    $tipe .= 'ss';
    $param[] = '%' . $cari . '%';
    $param[] = '%' . $cari . '%';
}
$where = $syarat ? 'WHERE ' . implode(' AND ', $syarat) : '';
// Satu pesanan = beberapa baris membeli dengan kode_pesanan sama, jadi dikelompokkan.
$daftar = db_semua(
    "SELECT kode_pesanan, MIN(tgl_pesanan) AS tgl_pesanan, MIN(nama_penerima) AS nama_penerima,
            MIN(metode_bayar) AS metode_bayar, SUM(jumlah * harga_satuan) AS total_bayar,
            MIN(status) AS status, MAX(id_membeli) AS urutan
     FROM membeli $where
     GROUP BY kode_pesanan
     ORDER BY urutan DESC",
    $tipe, $param
);

$judul = 'Pesanan';
$menu_aktif = 'pesanan';
require __DIR__ . '/../includes/admin_header.php';
?>
<div class="judul-halaman"><h1>Pesanan</h1></div>

<form method="get" class="row g-2 mb-3" role="search">
    <div class="col-sm-4 col-md-3">
        <label for="status" class="visually-hidden">Status</label>
        <select class="form-select" id="status" name="status">
            <option value="">Semua status</option>
            <?php foreach ($semua_status as $s): ?><option value="<?= $s ?>"<?= $s === $status ? ' selected' : '' ?>><?= e(nama_status($s)) ?></option><?php endforeach; ?>
        </select>
    </div>
    <div class="col-sm-5 col-md-4">
        <label for="q" class="visually-hidden">Cari penerima</label>
        <input type="search" class="form-control" id="q" name="q" value="<?= e($cari) ?>" placeholder="Nama atau email penerima">
    </div>
    <div class="col-auto"><button type="submit" class="btn btn-biru">Terapkan</button></div>
    <?php if ($status !== '' || $cari !== ''): ?><div class="col-auto"><a class="btn btn-garis" href="pesanan.php">Reset</a></div><?php endif; ?>
</form>

<section class="panel">
    <div class="table-responsive">
        <table class="table">
            <caption class="visually-hidden">Daftar pesanan</caption>
            <thead><tr><th scope="col">No.</th><th scope="col">Tanggal</th><th scope="col">Penerima</th><th scope="col">Metode</th><th scope="col" class="angka">Total</th><th scope="col">Status</th><th scope="col"><span class="visually-hidden">Aksi</span></th></tr></thead>
            <tbody>
            <?php if (!$daftar): ?><tr><td colspan="7" class="text-center text-muted py-4">Tidak ada pesanan.</td></tr><?php endif; ?>
            <?php foreach ($daftar as $p): ?>
                <tr>
                    <td><?= e($p['kode_pesanan']) ?></td>
                    <td><?= e(tanggal_indo($p['tgl_pesanan'], true)) ?></td>
                    <td><?= e($p['nama_penerima']) ?></td>
                    <td><?= e(nama_metode($p['metode_bayar'])) ?></td>
                    <td class="angka"><?= rupiah($p['total_bayar']) ?></td>
                    <td><?= label_status($p['status']) ?></td>
                    <td><a class="btn btn-garis btn-sm" href="pesanan_detail.php?kode=<?= urlencode($p['kode_pesanan']) ?>">Buka<span class="visually-hidden"> <?= e($p['kode_pesanan']) ?></span></a></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
<?php require __DIR__ . '/../includes/admin_footer.php'; ?>
